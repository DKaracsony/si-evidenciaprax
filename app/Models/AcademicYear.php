<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $table = 'academic_years';
    protected $fillable = ['start_date', 'end_date', 'season'];
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function internships()
    {
        return $this->hasMany(Internship::class, 'academic_year_id', 'id');
    }
}
