<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DashboardFilterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'filter' => 'required|in:all,year,month',
            'year' => 'required_if:filter,year,month|integer',
            'month' => 'required_if:filter,month|integer|between:1,12',
        ];
    }

    public function prepareForValidation(): void
    {
        if ($this->filter === 'all') {
            $this->merge([
                'year' => null,
                'month' => null,
            ]);
        } elseif ($this->filter === 'year') {
            $this->merge(['month' => null]);
        }
    }
}
