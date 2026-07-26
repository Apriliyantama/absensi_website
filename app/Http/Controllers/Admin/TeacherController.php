<?php

namespace App\Http\Controllers\Admin;

use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
            'name' => 'required',
            'nip' => 'required|unique:teachers,nip',
            'email' => 'required|email|unique:teachers,email|unique:users,email',
        ], [
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
        ]);

        // generate password
        $password = strtoupper(Str::random(3)) . rand(100, 999);

        // buat akun user (login)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role' => 'teacher',
            'status' => 'approved',
        ]);

        // simpan teacher + relasi user
        Teacher::create([
            'name'  => $request->name,
            'nip'   => $request->nip,
            'email' => $request->email,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data guru & akun berhasil dibuat',
            'password' => $password
        ]);
    }

    public function edit(int $id)
    {
        $teacher = Teacher::findOrFail($id);
        return response()->json($teacher);
    }

    public function update(Request $request, int $id)
    {
        $teacher = Teacher::findOrFail($id);
        $request->validate([
            'name' => 'required',

            'nip' => [
                'required',
                Rule::unique('teachers', 'nip')->ignore($teacher->id),
            ],

            'email' => [
                'required',
                'email',
                Rule::unique('teachers', 'email')->ignore($teacher->id),
                Rule::unique('users', 'email')->ignore($teacher->user_id),
            ],
        ]);

        $teacher->update([
            'name'  => $request->name,
            'nip'   => $request->nip,
            'email' => $request->email,
        ]);

        if ($teacher->user) {
            $teacher->user->update([
                'name'  => $request->name,
                'email' => $request->email,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data guru berhasil diupdate'
        ]);
    }

    public function destroy(int $id)
    {
        $teacher = Teacher::findOrFail($id);

        // hapus user terkait
        if ($teacher->user) {
            $teacher->user->delete();
        }

        $teacher->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data guru berhasil dihapus'
        ]);
    }
}
