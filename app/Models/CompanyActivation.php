<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyActivation extends Model
{
    protected $fillable = [
        'company_id',
        'hash',
        'sent_to_mail',
        'expires_at',
        'consumed_at',
        'revoked_at'
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'consumed_at' => 'datetime',
        'revoked_at'  => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function ownerProfile()
    {
        return $this->hasOne(\App\Models\CompanyOwnerProfile::class, 'company_activation_id');
    }
    public function scopeActive($query)
    {
        return $query
            ->whereNull('consumed_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now());
    }
}
