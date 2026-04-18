<?php

namespace App\Http\Controllers\Admin;

use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index()
    {
        return view('admin.teachers.index');
    }

    public function data()
    {
        return DataTables::of(Teacher::query())
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return '
                <button class="btn btn-sm btn-primary edit" data-id="' . $row->id . '">Edit</button>
                <button class="btn btn-sm btn-danger delete" data-id="' . $row->id . '">Delete</button>
            ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required',
            'nip'   => 'nullable',
            'email' => 'nullable|email',
        ]);

        Teacher::create([
            'name'  => $request->name,
            'nip'   => $request->nip,
            'email' => $request->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data guru berhasil ditambahkan'
        ]);
    }

    public function edit($id)
    {
        $teacher = Teacher::findOrFail($id);

        return response()->json($teacher);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'  => 'required',
            'nip'   => 'nullable',
            'email' => 'nullable|email',
        ]);

        $teacher = Teacher::findOrFail($id);

        $teacher->update([
            'name'  => $request->name,
            'nip'   => $request->nip,
            'email' => $request->email,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data guru berhasil diupdate'
        ]);
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);

        $teacher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data guru berhasil dihapus'
        ]);
    }
}
