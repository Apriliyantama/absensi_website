<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Classes;

class StudentController extends Controller
{
    public function index()
    {
        $classes = Classes::orderBy('grade')->get();

        return view('admin.students.index', compact('classes'));
    }

    public function data()
    {
        $students = Student::query()
            ->with([
                'user',
                'classRelation'
            ]);

        return DataTables::eloquent($students)

            ->addColumn('action', function ($row) {

                return '<a href="' .
                    route(
                        'admin.students.show',
                        $row->id
                    ) .
                    '"class="btn btn-info btn-sm">Detail</a>
                    
                    <button
                        class="btn btn-warning btn-sm editBtn"
                        data-id="' . $row->id . '">
                        Edit
                    </button>

                    <button class="btn btn-danger btn-sm delete"
                        data-id="' . $row->id . '">
                        Delete
                    </button>
                    ';
            })

            ->addIndexColumn()
            ->addColumn('student_name', function ($row) {
                return $row->name;
            })

            ->addColumn('email', function ($row) {
                return $row->user->email ?? '-';
            })

            ->orderColumn('email', function ($query, $order) {
                $query->leftJoin('users', 'users.id', '=', 'students.user_id')
                    ->select('students.*')
                    ->orderBy('users.email', $order);
            })

            ->addColumn('class_name', function ($row) {
                if (!$row->classRelation) {
                    return '-';
                }
                return $row->classRelation->grade . ' ' .
                    $row->classRelation->name;
            })

            ->orderColumn('class_name', function ($query, $order) {
                $query->leftJoin('classes', 'classes.id', '=', 'students.class_id')
                    ->select('students.*')
                    ->orderBy('classes.grade', $order)
                    ->orderBy('classes.name', $order);
            })

            ->addColumn('status_badge', function ($row) {
                $status = $row->user?->status;
                if ($status == 'approved') {
                    return '<span class="badge badge-success">Aktif</span>';
                }
                if ($status == 'pending') {
                    return '<span class="badge badge-warning">Pending</span>';
                }
                if ($status == 'rejected') {
                    return '<span class="badge badge-danger">Rejected</span>';
                }
                return '<span class="badge badge-secondary">Unknown</span>';
            })

            ->filterColumn('email', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('email', 'like', "%{$keyword}%");
                });
            })

            ->filterColumn('class_name', function ($query, $keyword) {
                $query->whereHas('classRelation', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('grade', 'like', "%{$keyword}%");
                });
            })

            ->rawColumns(['status_badge', 'action'])
            ->make(true);
    }

    public function show($id)
    {
        $student = Student::with([
            'user',
            'classRelation',
            'requestedClass',
        ])->findOrFail($id);

        return view(
            'admin.students.show',
            compact('student')
        );
    }

    public function edit($id)
    {
        $student = Student::findOrFail($id);
        return response()->json($student);
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'nis' => 'required',
            'class_id' => 'required|exists:classes,id',
        ]);

        $student->update([
            'name' => $request->name,
            'nis' => $request->nis,
            'class_id' => $request->class_id,
            'gender' => $request->gender,
            'birth_date' => $request->birth_date,
            'address' => $request->address,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data siswa berhasil diperbarui'
        ]);
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        // hapus user terkait
        if ($student->user) {
            $student->user->delete();
        }

        $student->delete();

        return response()->json([
            'success' => true
        ]);
    }

    public function promote(Request $request)
    {
        Student::where(
            'class_id',
            $request->from_class
        )->update([
            'class_id' => $request->to_class
        ]);

        return response()->json([
            'success' => true
        ]);
    }
}
