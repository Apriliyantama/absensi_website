<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Student;

class AuthController extends Controller
{
    // login akun murid
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }

        // validasi approval
        if ($user->status !== 'approved') {
            return response()->json([
                'message' => 'Akun belum disetujui admin'
            ], 403);
        }

        $token = $user->createToken('mobile-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user,
        ], 200);
    }

    // register akun murid
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'nis' => 'required|unique:students,nis',
            'gender' => 'required',
            'birth_date' => 'required|date',
            'address' => 'required',
            'class_id' => 'required|exists:classes,id',
        ]);

        // simpan user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'student',
            'status' => 'pending',
            'class_id' => null,
        ]);

        // simpan student
        Student::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'nis' => $request->nis,

            'gender' => $request->gender,
            'birth_date' => $request->birth_date,
            'address' => $request->address,

            'requested_class_id' => $request->class_id,
        ]);

        return response()->json([
            'message' => 'Register berhasil, tunggu approval admin'
        ], 200);
    }
}
