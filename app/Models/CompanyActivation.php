<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CompanyActivation extends Model
{
    protected $table = 'company_activations';
    public $timestamps = false;

    protected $fillable = [
        'hash',
        'sent_to_mail',
        'created_at',
        'consumed_at',
    ];

    protected $casts = [
        'created_at'  => 'datetime',
        'consumed_at' => 'datetime',
    ];

    public function ownerProfile(): HasOne
    {
        return $this->hasOne(\App\Models\CompanyOwnerProfile::class, 'company_activation_id');
    }

    public function scopeActive($query)
    {
        return $query
            ->whereNull('consumed_at')
            ->where('created_at', '>', now()->subHours(48));
    }
}
