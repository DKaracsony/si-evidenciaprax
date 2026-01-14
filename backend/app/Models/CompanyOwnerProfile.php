<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyOwnerProfile extends Model
{
    protected $fillable = [
        'is_active',
        'company_id',
        'company_activation_id',
        'company_user_id',
        'role_at_company'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function activation()
    {
        return $this->belongsTo(CompanyActivation::class, 'company_activation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'company_user_id');
    }
}

