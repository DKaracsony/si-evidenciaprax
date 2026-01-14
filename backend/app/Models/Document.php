<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    public const TYPE_AGREEMENT = 'agreement';
    public const TYPE_STATEMENT = 'statement';
    public const TYPE_INVOICE = 'invoice';
    public const TYPE_SALARY = 'salary_statement';
    protected $fillable = [
        'file_name',
        'file_path',
        'type',
        'invoice_month',
        'internship_id',
        'uploaded_by_user_id',
        'document_status_id',
    ];

    public function internship()
    {
        return $this->belongsTo(Internship::class);
    }

    public function uploadedByUser()
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function status()
    {
        return $this->belongsTo(DocumentStatus::class, 'document_status_id');
    }
}

