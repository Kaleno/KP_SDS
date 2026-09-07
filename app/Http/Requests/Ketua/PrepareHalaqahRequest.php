<?php

namespace App\Http\Requests\Ketua;

use App\Enums\Gender;
use App\Models\User;
use App\Support\Role;
use App\Support\WeekDay;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class PrepareHalaqahRequest extends FormRequest
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
            'year_source' => ['required', 'in:existing,new'],
            'academic_year_id' => ['required_if:year_source,existing', 'nullable', 'exists:academic_years,id'],
            'year_name' => ['required_if:year_source,new', 'nullable', 'string', 'max:100'],
            'year_start_date' => ['required_if:year_source,new', 'nullable', 'date'],
            'year_end_date' => ['required_if:year_source,new', 'nullable', 'date', 'after:year_start_date'],

            'location_source' => ['required', 'in:existing,new'],
            'location_id' => ['required_if:location_source,existing', 'nullable', 'exists:locations,id'],
            'location_name' => ['required_if:location_source,new', 'nullable', 'string', 'max:100'],
            'location_description' => ['nullable', 'string', 'max:255'],

            'ustaz_source' => ['required', 'in:existing,new'],
            'ustaz_user_id' => [
                'required_if:ustaz_source,existing',
                'nullable',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('id', User::role(Role::Ustaz)->pluck('id'));
                }),
            ],
            'ustaz_name' => ['required_if:ustaz_source,new', 'nullable', 'string', 'max:255'],
            'ustaz_username' => ['required_if:ustaz_source,new', 'nullable', 'string', 'max:50', 'alpha_dash', 'unique:'.User::class.',username'],
            'ustaz_email' => ['nullable', 'email', 'max:255', 'unique:'.User::class.',email'],
            'ustaz_password' => ['required_if:ustaz_source,new', 'nullable', 'confirmed', Password::defaults()],

            'santri_source' => ['required', 'in:existing,new'],
            'santri_id' => ['required_if:santri_source,existing', 'nullable', 'exists:santri_profiles,id'],
            'santri_name' => ['required_if:santri_source,new', 'nullable', 'string', 'max:255'],
            'santri_nis' => ['required_if:santri_source,new', 'nullable', 'string', 'max:30', 'unique:users,username', 'unique:santri_profiles,nis'],
            'santri_email' => ['nullable', 'email', 'max:255', 'unique:'.User::class.',email'],
            'santri_gender' => ['required_if:santri_source,new', 'nullable', Rule::enum(Gender::class)],
            'santri_password' => ['required_if:santri_source,new', 'nullable', 'confirmed', Password::defaults()],

            'halaqah_name' => ['required', 'string', 'max:100'],
            'day_of_week' => ['required', 'integer', 'in:'.implode(',', array_keys(WeekDay::labels()))],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['ustaz_email', 'santri_email', 'location_description'] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }
    }
}
