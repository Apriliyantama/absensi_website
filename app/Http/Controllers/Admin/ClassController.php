<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classes;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ClassController extends Controller
{
    public function index()
    {
        return view('admin.classes.index');
    }

    public function data()
    {
        $query = Classes::query();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('full_name', function ($row) {
                return $row->grade . ' ' . $row->name;
            })

            ->filter(function ($query) {
                if (request()->has('search') && $search = request('search')['value']) {

                    $query->where(function ($q) use ($search) {
                        $q->where('grade', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orwhereRaw("CONCAT(grade, ' ', name) LIKE ?", ["%{$search}%"]);
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
        Classes::create($request->all());

        return response()->json(['success' => true]);
    }

    public function edit($id)
    {
        return response()->json(Classes::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        Classes::findOrFail($id)->update($request->all());

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        Classes::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
