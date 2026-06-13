<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:kids,adult'],
            'schedule_slots' => ['required', 'array', 'min:1'],
            'schedule_slots.*.weekday' => ['required', 'integer', 'between:1,7'],
            'schedule_slots.*.start_time' => ['required', 'date_format:H:i'],
            'schedule_slots.*.end_time' => ['nullable', 'date_format:H:i'],
            'active' => ['boolean'],
        ];
    }
}
