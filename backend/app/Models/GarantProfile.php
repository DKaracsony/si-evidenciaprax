<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GarantProfile extends Model
{
    protected $table = 'garant_profiles';
    protected $fillable = ['garant_user_id'];
    public $timestamps = false;


    public function user()
    {
        return $this->belongsTo(User::class, 'garant_user_id', 'id');
    }

    public function faculties()
    {
        return $this->belongsToMany(
            Faculty::class,
            'garant_faculties',
            'garant_profile_id',
            'faculty_id'
        );
    }

}
