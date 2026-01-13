<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentStatus extends Model
{
    protected $table = 'document_statuses';

    public $timestamps = false;

    protected $fillable = [
        'note',
        'reviewer_user_id',
        'decision',
        'created_at',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'document_status_id');
    }
}
