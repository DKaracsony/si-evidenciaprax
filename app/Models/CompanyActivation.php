<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyActivation extends Model
{
    public $timestamps = false;

    protected $fillable = ['hash', 'sent_to_mail', 'consumed_at'];

    public function ownerProfiles()
    {
        return $this->hasMany(CompanyOwnerProfile::class);
    }
}

