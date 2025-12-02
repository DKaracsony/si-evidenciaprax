<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    protected $table = 'users';

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'title_before',
        'title_after',
        'email',
        'password_hash',
        'password_reset_needed',
        'phone_number',
        'role_id',
        'active',
        'last_login_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'password_reset_needed' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // get all permissions of user through role
    public function allPermissions()
    {
        return $this->role ? $this->role->permissions : collect();
    }

    public function studentProfile(){
        return $this->hasOne(StudentProfile::class, 'student_user_id');
    }

    public function companyOwnerProfile()
    {
        return $this->hasOne(\App\Models\CompanyOwnerProfile::class, 'company_user_id');
    }

    public function getAuthPassword() // override to use password_hash
    {
        return $this->password_hash;
    }

    protected function userAddress() : Attribute// dynamic attribute to get address from either studentProfile or companyOwnerProfile
    {
        return Attribute::make(
            get: fn() => $this->studentProfile?->address
                ?? $this->companyOwnerProfile?->company?->address
                ?? null
        );
    }

    public function internshipStatusHistories()
    {
        return $this->hasMany(InternshipStatusHistory::class, 'changed_by_user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'receiver_user_id', 'id');
    }
}
