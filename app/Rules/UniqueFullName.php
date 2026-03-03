<?php

namespace App\Rules;

use App\Models\Personnel;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class UniqueFullName implements ValidationRule
{
    protected $id;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    /**
     * Run the validation rule.
     * Validates uniqueness of fname + lname combination matching database constraint.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $fname = request('fname');
        $lname = request('lname');

        $query = Personnel::where('fname', $fname)
            ->where('lname', $lname);

        if($this->id)
            $query->where('id', '!=', $this->id);

        if ($query->exists())
            $fail('Already exists');
    }
}
