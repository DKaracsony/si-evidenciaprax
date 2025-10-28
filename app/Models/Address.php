<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $table = 'addresses';
    protected $fillable = [
        'city',
        'street',
        'house_number',
        'postal_code',
        'country_id'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function studentProfile(){
        return $this->hasOne(StudentProfile::class, 'address_id');
    }
}
