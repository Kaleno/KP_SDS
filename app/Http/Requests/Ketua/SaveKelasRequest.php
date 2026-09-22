<?php

namespace App\Http\Requests\Ketua;

use App\Models\User;
use App\Support\Role;
use App\Support\WeekDay;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveKelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-master') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'ustaz_user_id' => [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('id', User::role(Role::teaching())->where('is_active', true)->pluck('id'));
                }),
            ],
            'days' => ['required', 'array', 'min:1'],
            'days.*' => ['integer', Rule::in(array_keys(WeekDay::labels()))],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'days.required' => 'Pilih minimal satu hari pembelajaran.',
            'days.min' => 'Pilih minimal satu hari pembelajaran.',
            'ustaz_user_id.required' => 'Pilih pengajar untuk kelas ini.',
            'end_time.after' => 'Jam selesai harus setelah jam mulai.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $start = $this->input('start_time');
        $end = $this->input('end_time');
        $days = $this->input('days', []);

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'start_time' => is_string($start) ? substr($start, 0, 5) : $start,
            'end_time' => is_string($end) ? substr($end, 0, 5) : $end,
            'days' => is_array($days) ? array_values(array_unique(array_map('intval', $days))) : [],
        ]);
    }
}
