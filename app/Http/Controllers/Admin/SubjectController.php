<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index()
    {
        return view('admin.subjects.index');
    }

    public function data()
    {
        $query = Subject::query();

        return DataTables::of($query)
            ->addIndexColumn()
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
            'name' => 'required|unique:subjects,name'
        ], [
            'name.required' => 'Nama mata pelajaran wajib diisi.',
            'name.unique' => 'Mata pelajaran sudah ada.'
        ]);

        Subject::create([
            'name' => $request->name
        ]);

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        return response()->json(Subject::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);
        $request->validate([
            'name' => [
                'required',
                Rule::unique('subjects', 'name')->ignore($subject->id)
            ]
        ], [
            'name.required' => 'Nama mata pelajaran wajib diisi.',
            'name.unique' => 'Mata pelajaran sudah ada.'
        ]);

        $subject->update([
            'name' => $request->name
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        Subject::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
