<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class StudentApprovalController extends Controller
{
    public function index()
    {
        return view('admin.students.approval');
    }

    public function data()
    {
        $query = User::with(['student.classRelation', 'student.requestedClass'])
            ->where('role', 'student')
            ->where('status', 'pending')
            ->whereHas('student');

        $classes = \App\Models\Classes::all();

        return DataTables::of($query)
            ->addIndexColumn()

            ->addColumn('nis', function ($row) {
                return $row->student->nis ?? '-';
            })

            ->addColumn('class', function ($row) {
                if ($row->student && $row->student->classRelation) {
                    return $row->student->classRelation->grade . ' ' .
                        $row->student->classRelation->name;
                }
                return '-';
            })

            ->addColumn('requested_class', function ($row) {
                if ($row->student && $row->student->requestedClass) {
                    return $row->student->requestedClass->grade . ' ' .
                        $row->student->requestedClass->name;
                }
                return '-';
            })

            ->addColumn('class_dropdown', function ($row) use ($classes) {

                $html = '<select class="form-control class-select" data-id="' . $row->id . '">';

                foreach ($classes as $class) {
                    $selected = ($row->student->requested_class_id == $class->id) ? 'selected' : '';
                    $html .= '<option value="' . $class->id . '" ' . $selected . '>'
                        . $class->grade . ' ' . $class->name .
                        '</option>';
                }

                $html .= '</select>';

                return $html;
            })

            ->addColumn('action', function ($row) {
                return '
                <button class="btn btn-success btn-sm approve" data-id="' . $row->id . '">Approve</button>
                <button class="btn btn-danger btn-sm reject" data-id="' . $row->id . '">Reject</button>
            ';
            })

            ->rawColumns(['action', 'class_dropdown'])
            ->make(true);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id'
        ]);

        $user = User::with('student')->findOrFail($id);

        //  cek student
        if (!$user->student) {
            return response()->json([
                'message' => 'Data student tidak ditemukan'
            ], 422);
        }

        // update status
        $user->update([
            'status' => 'approved',
            'class_id' => $request->class_id,
        ]);

        // sync
        $user->student->update([
            'class_id' => $request->class_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil approve'
        ]);
    }

    public function reject($id)
    {
        $user = User::findOrFail($id);

        $user->status = 'rejected';
        $user->save();

        return response()->json(['success' => true]);
    }
}
