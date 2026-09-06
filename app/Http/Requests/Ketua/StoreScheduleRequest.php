<?php

namespace App\Http\Requests\Ketua;

use App\Models\Halaqah;
use App\Services\ScheduleConflictChecker;
use App\Support\WeekDay;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreScheduleRequest extends FormRequest
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
            'location_id' => ['required', 'exists:locations,id'],
            'day_of_week' => ['required', 'integer', 'in:'.implode(',', array_keys(WeekDay::labels()))],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Halaqah $halaqah */
            $halaqah = $this->route('halaqah');
            $checker = app(ScheduleConflictChecker::class);

            if ($checker->ustazOverlaps(
                $halaqah,
                (int) $this->input('day_of_week'),
                $this->string('start_time')->toString().':00',
                $this->string('end_time')->toString().':00',
            )) {
                $validator->errors()->add('start_time', 'Jam ini bentrok dengan jadwal ustaz yang sama.');
            }
        });
    }
}
