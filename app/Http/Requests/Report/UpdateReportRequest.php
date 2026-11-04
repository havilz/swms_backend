<?php

namespace App\Http\Requests\Report;

use App\Models\Report;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $report = $this->route('report');

        return $report instanceof Report
            && $this->user()?->isUser()
            && $report->user_id === $this->user()->id;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'location_address' => ['sometimes', 'required', 'string', 'max:255'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'image' => ['sometimes', 'nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'prohibits:remove_image'],
            'remove_image' => ['sometimes', 'boolean', 'prohibits:image'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->safe()->all() === []) {
                    $validator->errors()->add('report', 'Minimal satu perubahan laporan harus dikirim.');
                }
            },
        ];
    }
}
