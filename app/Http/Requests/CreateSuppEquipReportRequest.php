<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class CreateSuppEquipReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $templates = config('suppequipreportforms', []);
        $reportType = $this->input('report_type');
        $template = Arr::get($templates, $reportType, []);
        $fields = Arr::get($template, 'fields', []);

        $rules = [
            'transaction_id' => ['required', 'uuid', 'exists:transactions,id'],
            'report_type' => ['required', 'string', Rule::in(array_keys($templates))],
            'report_data' => ['required', 'array'],
            'notes' => ['nullable', 'string'],
            'reported_at' => ['nullable', 'date'],
        ];

        foreach ($fields as $field => $definition) {
            $rules["report_data.$field"] = Arr::get($definition, 'rules', 'nullable');
        }

        $rules['transaction_id'][] = Rule::unique('supp_equip_reports')
            ->where(fn ($query) => $query
                ->where('report_type', $this->input('report_type'))
                ->whereNull('deleted_at')
            );

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'transaction_id.unique' => 'A report for this transaction and template already exists.',
        ];

        $template = config('suppequipreportforms.' . $this->input('report_type'), []);
        $fields = $template['fields'] ?? [];

        foreach ($fields as $field => $definition) {
            $label = Arr::get($definition, 'label', ucwords(str_replace('_', ' ', $field)));
            $messages["report_data.$field.required"] = "$label is required.";
        }

        return $messages;
    }
}
