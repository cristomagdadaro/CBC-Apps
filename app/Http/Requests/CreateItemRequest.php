<?php

namespace App\Http\Requests;

use App\Models\Item;
use App\Rules\UniqueItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CreateItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        do {
            $temp = Str::uuid()->toString();
        } while (Item::where('id', $temp)->exists());

        $this->merge([
            'id' => $temp,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required','string', new UniqueItem()],
            'brand' => ['required','string', new UniqueItem()],
            'description' => 'string|nullable',
            'specifications' => 'string|nullable',
            'category_id' => ['required','exists:categories,id', new UniqueItem()],
            'supplier_id' => 'required|exists:suppliers,id',
            'image' => 'string|nullable',
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
