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
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $fname = request('fname');
        $mname = request('mname');
        $lname = request('lname');
        $suffix = request('suffix');

        $query = Personnel::where('fname', $fname)
            ->where('mname', $mname)
            ->where('lname', $lname)
            ->where('suffix', $suffix);

        if($this->id)
            $query->where('id', '!=', $this->id);

        if ($query->exists())
            $fail('Personnel already exists');
    }
}
