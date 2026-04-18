<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Teacher;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Teacher::create([
            'name' => 'Zeno',
            'nip' => '123456',
            'email' => 'zeno@gmail.com'
        ]);

        Teacher::create([
            'name' => 'Mawar',
            'nip' => '654321',
            'email' => 'mawar@gmail.com'
        ]);
    }
}
