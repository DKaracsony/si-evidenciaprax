<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    public const CREATED = "Vytvorená";
    public const ACCEPTED = "Potvrdená";
    public const REJECTED = "Zamietnutá";
    public const APPROVED = "Schválená";
    public const DEFENDED = "Obhájená";
    public const UNDEFENDED = "Neobhájená";

    protected $table = 'statuses';
    protected $fillable = ['name', 'order_index'];

    public function internshipStatusHistories()
    {
        return $this->hasMany(InternshipStatusHistory::class, 'status_id');
    }
}
