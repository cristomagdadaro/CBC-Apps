<?php

namespace App\Http\Requests;

use App\Models\RequestFormPivot;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequestFormPivot extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('fes.request.approve') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'request_status' => 'required|in:' . implode(',', [
                RequestFormPivot::STATUS_PENDING,
                RequestFormPivot::STATUS_APPROVED,
                RequestFormPivot::STATUS_RELEASED,
                RequestFormPivot::STATUS_RETURNED,
                RequestFormPivot::STATUS_REJECTED,
            ]),
            'disapproved_remarks' => 'nullable|string|max:5000',
            'approval_constraint' => 'nullable|string|max:5000',
        ];
    }
}
