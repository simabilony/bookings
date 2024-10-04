<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
        ];
    }

    public function authorize(): bool
    {
        return $this->route()->booking->user_id === auth()->id();
    }
}
