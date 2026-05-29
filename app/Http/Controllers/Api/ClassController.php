<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Classes;

class ClassController extends Controller
{
    public function index()
    {
        $classes = Classes::select(
            'id',
            'grade',
            'name'
        )
        ->orderBy('grade')
        ->get();

        return response()->json($classes);
    }
}