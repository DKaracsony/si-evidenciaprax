<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
{
    public function rules(): array
    {
        return match ($this->route()?->getName()) {
            'documents.reports.upload' => $this->reportRules(),

            default => $this->defaultRules(),
        };
    }

    private function defaultRules(): array
    {
        return [
            'internship_id' => [
                'required',
                'integer',
                'exists:internships,id',
            ],

            'document' => [
                'required',
                'file',
                'mimes:pdf',
                'max:10240', // 10 MB in KB
            ],

            'document.*' => ['prohibited'],
        ];
    }

    private function reportRules(): array
    {
        return [
            'document' => [
                'required',
                'file',
                'mimes:pdf',
                'max:10240', // 10 MB in KB
            ],

            'document.*' => ['prohibited'],
        ];
    }
}
