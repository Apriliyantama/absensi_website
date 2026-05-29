<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Yajra\DataTables\Facades\DataTables;

class StudentController extends Controller
{
    public function index()
    {
        return view('admin.students.index');
    }

    public function data()
    {
        $students = Student::query()
            ->with([
                'user',
                'classRelation'
            ]);

        return DataTables::eloquent($students)

            ->addIndexColumn()

            ->addColumn('student_name', function ($row) {
                return $row->name;
            })

            ->addColumn('email', function ($row) {
                return $row->user->email ?? '-';
            })

            ->addColumn('class_name', function ($row) {

                if (!$row->classRelation) {
                    return '-';
                }

                return $row->classRelation->grade . ' ' .
                    $row->classRelation->name;
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

            ->rawColumns(['status_badge'])

            ->make(true);
    }
}
