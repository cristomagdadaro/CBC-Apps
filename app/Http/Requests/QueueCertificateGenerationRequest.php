<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QueueCertificateGenerationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $useSavedTemplate = $this->boolean('use_saved_template');
        $useEventData = $this->boolean('use_event_data');

        return [
            'template' => [
                $useEventData ? 'nullable' : ($useSavedTemplate ? 'nullable' : 'required'),
                'file',
                'mimes:pptx',
                'max:10240',
            ],
            'use_saved_template' => ['nullable', 'boolean'],
            'data' => [
                $useEventData ? 'nullable' : 'required',
                'file',
                'max:10240',
                function ($attribute, $value, $fail) {
                    if (!$value instanceof \Illuminate\Http\UploadedFile) {
                        return;
                    }
                    $ext = strtolower((string) $value->getClientOriginalExtension());
                    if (!in_array($ext, ['xlsx', 'csv'], true)) {
                        $fail('The data field must be a file of type: xlsx, csv.');
                    }
                },
            ],
            'use_event_data' => ['nullable', 'boolean'],
            'name_column' => ['nullable', 'string', 'max:150', $useEventData ? 'required' : 'nullable'],
            'email_column' => ['nullable', 'string', 'max:150', $useEventData ? 'required' : 'nullable'],
            'subform_type' => ['nullable', 'string', 'max:50'],
            'recipient_ids' => [$useEventData ? 'nullable' : 'prohibited', 'array'],
            'recipient_ids.*' => [$useEventData ? 'nullable' : 'prohibited', 'string', 'max:64'],
            'format' => ['required', 'string', 'in:pptx,pdf,png,jpg'],
            'name_template' => ['nullable', 'string', 'max:150'],
            'event_title' => ['nullable', 'string', 'max:255'],
            'event_date' => ['nullable', 'string', 'max:150'],
            'date_given' => ['nullable', 'string', 'max:150'],
        ];
    }
}
