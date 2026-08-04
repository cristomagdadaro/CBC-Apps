<?php

namespace App\Http\Requests;

use App\Rules\UniqueItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required','string', new UniqueItem($this->id)],
            'brand' => ['required','string', new UniqueItem($this->id)],
            'description' => ['string','nullable', new UniqueItem($this->id)],
            'specifications' => ['string','nullable'],
            'category_id' => ['required','exists:categories,id', new UniqueItem($this->id)],
            'supplier_id' => ['required','exists:suppliers,id'],
            'image' => ['string','nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Required field',
            'brand.required' => 'Required field',
            'category_id.required' => 'Required field',
            'supplier_id.required' => 'Required field',
            'category_id.exists' => 'Category does not exist',
            'supplier_id.exists' => 'Supplier does not exist',
        ];
    }
}
