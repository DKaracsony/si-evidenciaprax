<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property-read \App\Models\StudentProfile $studentProfile
 * @property-read \App\Models\Company $company
 * @property-read \App\Models\AcademicYear $academicYear
 */

class Internship extends Model
{
    public const PRACTICE_TYPE_STANDARD = 'standard';
    public const PRACTICE_TYPE_PAID_EMPLOYMENT_CONTRACT = 'paid_employment_contract';
    public const PRACTICE_TYPE_PAID_INVOICES = 'paid_invoices';

    protected $table = 'internships';
    protected $fillable = ['student_profile_id', 'company_id', 'academic_year_id', 'start_date', 'date_to', 'description', 'is_draft', 'submitted_at', 'practice_type'];

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id', 'id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'id');
    }

    public function internshipStatusHistories()
    {
        return $this->hasMany(InternshipStatusHistory::class, 'internship_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'internship_id', 'id');
    }

    public function agreementDocument()
    {
        return $this->hasOne(Document::class, 'internship_id', 'id')->where('type', 'agreement');
    }

    public function scopeActive($query)
    {
        return $query->where('is_draft', false);
    }

}
