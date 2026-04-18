<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Classes;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // ambil class pertama
        $class = Classes::first();

        // user 1
        $user1 = User::create([
            'name' => 'Ibnu',
            'email' => 'ibnu@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 'student',
            'status' => 'pending'
        ]);

        Student::create([
            'user_id' => $user1->id,
            'name' => 'Ibnu',
            'nis' => '1000',
            'class_id' => $class->id, // 🔥 relasi penting
        ]);

        // user 2
        $user2 = User::create([
            'name' => 'Siti',
            'email' => 'siti@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 'student',
            'status' => 'pending'
        ]);

        Student::create([
            'user_id' => $user2->id,
            'name' => 'Siti',
            'nis' => '1001',
            'class_id' => $class->id,
        ]);
    }
}
