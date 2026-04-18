<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

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
            'name' => 'required'
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
        $request->validate([
            'name' => 'required'
        ]);

        Subject::findOrFail($id)->update([
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
