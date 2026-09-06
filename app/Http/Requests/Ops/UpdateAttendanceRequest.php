<?php

namespace App\Http\Requests\Ops;

use App\Enums\AttendanceStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('operate-daily') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rows' => ['present', 'array'],
            'rows.*.status' => ['required', Rule::enum(AttendanceStatus::class)],
            'rows.*.note' => ['nullable', 'string', 'max:255'],
        ];
    }
}
