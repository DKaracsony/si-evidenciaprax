<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternshipStatusHistory extends Model
{
    protected $table = 'internship_status_histories';
    public $timestamps = false;
    protected $fillable = ['internship_id', 'status_id', 'explanation', 'status_changed_at', 'changed_by_user_id'];

    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }

    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by_user_id', 'id');
    }

    public function internship()
    {
        return $this->belongsTo(Internship::class, 'internship_id', 'id');
    }
}
