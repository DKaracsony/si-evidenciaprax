<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewReportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'decision' => ['required', 'in:approved,rejected'],
            'note' => ['required', 'string', 'max:1000'],
        ];
    }
}
