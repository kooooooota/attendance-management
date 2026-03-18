<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'punched_in_at' => 'nullable|date_format:H:i',
            'punched_out_at' => 'nullable|date_format:H:i|after:punched_in_at',
            'remarks' => 'required|max:255',

            'breaks.*.punched_in_at' => [
                'nullable',
                'date_format:H:i',
                'after_or_equal:punched_in_at',
                'before_or_equal:punched_out_at',
            ],
            'breaks.*.punched_out_at' => [
                'nullable',
                'date_format:H:i',
                'after:breaks.*.punched_in_at',
                'before_or_equal:punched_out_at',
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $breaks = $this->input('breaks', []);

            $sortedBreaks = collect($breaks)->filter(function($break) {
                return !empty($break['punched_in_at']) && !empty($break['punched_out_at']);
            })->sortBy('punched_in_at')->values();

            for ($i = 0; $i < $sortedBreaks->count() - 1; $i++) {
                $currentEnd = $sortedBreaks[$i]['punched_out_at'];
                $nextStart = $sortedBreaks[$i + 1]['punched_in_at'];

                if ($currentEnd > $nextStart) {
                    $validator->errors()->add('breaks', '休憩時間が重なっています。時間を調整してください。');
                    break;
                }
            }
        });
    }

    public function messages()
    {
        return [
            'punched_out_at.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'remarks.required' => '備考を記入してください',

            'breaks.*.punched_in_at.after_or_equal' => '休憩時間が不適切な値です',
            'breaks.*.punched_in_at.before_or_equal' => '休憩時間が不適切な値です',
            'breaks.*.punched_out_at.after' => '休憩時間が不適切な値です',
            'breaks.*.punched_out_at.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
        ];
    }
}
