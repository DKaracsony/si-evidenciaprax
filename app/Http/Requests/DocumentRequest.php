<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentRequest extends FormRequest
{
    public function rules(): array
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
        ];
    }
}
