<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $table = 'student_profiles';

    protected $fillable = [
      'student_email',
      'faculty_id',
      'address_id',
      'student_user_id',
    ];

    public function address(){
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function faculty(){
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'student_user_id');
    }
}
