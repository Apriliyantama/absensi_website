<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'nis',
        // 'class',
        'gender',
        'birth_date',
        'address',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classRelation()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function requestedClass()
    {
        return $this->belongsTo(Classes::class, 'requested_class_id');
    }
}
