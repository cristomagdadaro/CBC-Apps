<?php

namespace App\Rules;

use App\Models\Item;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class UniqueItem implements ValidationRule
{

    protected $id;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $name = request('name');
        $brand = request('brand');
        $category_id = request('category_id');

        $query = Item::where('name', $name)
            ->where('brand', $brand)
            ->where('category_id', $category_id);

        if($this->id)
            $query->where('id', '!=', $this->id);

        if ($query->exists())
            $fail('Item already exists');


    }
}
