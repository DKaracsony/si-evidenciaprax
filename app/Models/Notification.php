<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public const STATUS_CHANGED = 'status_changed';
    public const INFORMATION = 'information';
    public const COMPLETED = 'completed';

    protected $table = 'notifications';
    public $timestamps = false;
    protected $fillable = [
        'text',
        'type',
        'emailed_at',
        'seen_at',
        'sent_at',
        'receiver_user_id',
    ];

    public function receiverUser()
    {
        return $this->belongsTo(User::class, 'receiver_user_id', 'id');
    }
}
