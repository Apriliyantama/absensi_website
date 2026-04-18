<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LessonSchedule;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ScheduleController extends Controller
{
    public function index()
    {
        $classes = Classes::all();
        $subjects = Subject::all();
        $teachers = Teacher::all();

        return view('admin.schedules.index', compact('classes', 'subjects', 'teachers'));
    }

    public function data(Request $request)
    {
        $query = LessonSchedule::with(['class', 'subject', 'teacher']);

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn(
                'class',
                fn($row) =>
                $row->class->grade . ' ' . $row->class->name
            )

            ->addColumn('subject', fn($row) => $row->subject->name)

            ->addColumn('teacher', fn($row) => $row->teacher->name)

            ->addColumn('day', function ($row) {
                $days = [
                    1 => 'Senin',
                    2 => 'Selasa',
                    3 => 'Rabu',
                    4 => 'Kamis',
                    5 => 'Jumat',
                    6 => 'Sabtu',
                    7 => 'Minggu'
                ];
                return $days[$row->day_of_week] ?? '-';
            })

            ->addColumn(
                'time',
                fn($row) =>
                $row->start_time . ' - ' . $row->end_time
            )

            // Seacrh
            ->filter(function ($query) use ($request) {

                if ($search = $request->get('search')['value']) {

                    $query->where(function ($q) use ($search) {

                        // class
                        $q->whereHas('class', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%")
                                ->orWhere('grade', 'like', "%{$search}%");
                        });

                        // subject
                        $q->orWhereHas('subject', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%");
                        });

                        // teacher
                        $q->orWhereHas('teacher', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%");
                        });

                        // hari
                        $days = [
                            'senin' => 1,
                            'selasa' => 2,
                            'rabu' => 3,
                            'kamis' => 4,
                            'jumat' => 5,
                            'sabtu' => 6,
                            'minggu' => 7
                        ];

                        $searchDay = strtolower($search);
                        if (isset($days[$searchDay])) {
                            $q->orWhere('day_of_week', $days[$searchDay]);
                        }

                        // jam
                        $q->orWhere('start_time', 'like', "%{$search}%")
                            ->orWhere('end_time', 'like', "%{$search}%");
                    });
                }
            })

            ->addColumn('action', function ($row) {
                return '
                <button class="btn btn-warning btn-sm edit" data-id="' . $row->id . '">Edit</button>
                <button class="btn btn-danger btn-sm delete" data-id="' . $row->id . '">Delete</button>
            ';
            })

            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_id' => 'required',
            'subject_id' => 'required',
            'teacher_id' => 'required',
            'day_of_week' => 'required',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        $exists = LessonSchedule::where('day_of_week', $request->day_of_week)
            ->where(function ($q) use ($request) {

                // bentrok kelas
                $q->where(function ($q2) use ($request) {
                    $q2->where('class_id', $request->class_id)
                        ->where(function ($time) use ($request) {
                            $time->where('start_time', '<', $request->end_time)
                                ->where('end_time', '>', $request->start_time);
                        });
                })

                    // bentrok guru
                    ->orWhere(function ($q2) use ($request) {
                        $q2->where('teacher_id', $request->teacher_id)
                            ->where(function ($time) use ($request) {
                                $time->where('start_time', '<', $request->end_time)
                                    ->where('end_time', '>', $request->start_time);
                            });
                    });
            })
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Jadwal bentrok harap periksa kelas dan guru'
            ], 422);
        }

        LessonSchedule::create($request->all());

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        return response()->json(LessonSchedule::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'class_id' => 'required',
            'subject_id' => 'required',
            'teacher_id' => 'required',
            'day_of_week' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $exists = LessonSchedule::where('day_of_week', $request->day_of_week)
            ->where('id', '!=', $id) // exclude dirinya sendiri
            ->where(function ($q) use ($request) {

                $q->where(function ($q2) use ($request) {
                    $q2->where('class_id', $request->class_id)
                        ->where(function ($time) use ($request) {
                            $time->where('start_time', '<', $request->end_time)
                                ->where('end_time', '>', $request->start_time);
                        });
                })

                    ->orWhere(function ($q2) use ($request) {
                        $q2->where('teacher_id', $request->teacher_id)
                            ->where(function ($time) use ($request) {
                                $time->where('start_time', '<', $request->end_time)
                                    ->where('end_time', '>', $request->start_time);
                            });
                    });
            })
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Jadwal bentrok!'
            ], 422);
        }

        LessonSchedule::findOrFail($id)->update($request->all());

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        LessonSchedule::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
