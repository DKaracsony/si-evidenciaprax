<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyActivation extends Model
{
    protected $fillable = ['hash', 'sent_to_mail', 'consumed_at'];

    public function ownerProfiles()
    {
        return $this->hasOne(CompanyOwnerProfile::class);
    }
}

