<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $table = 'faculties';

    protected $fillable = [
        'name',
        'active',
    ];

    public function studentProfile()
    {
        return $this->hasMany(StudentProfile::class, 'faculty_id');
    }
}
