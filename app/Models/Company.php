<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['name', 'description', 'website', 'address_id'];

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function ownerProfiles()
    {
        return $this->hasOne(CompanyOwnerProfile::class);
    }
}

