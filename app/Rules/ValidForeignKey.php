<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Classes\Common;
use Illuminate\Support\Facades\DB;

class ValidForeignKey implements ValidationRule
{
    protected $table;
    protected $message;

    public function __construct($table)
    {
        $this->table = $table;
        $this->message = 'The selected value is invalid.';
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (is_null($value) || $value === '') {
            return;
        }

        $id = Common::getIdFromHash($value);
        if (!$id) {
            $fail('Invalid hashid for ' . $attribute . '.');
            return;
        }

        if (!DB::table($this->table)->where('id', $id)->exists()) {
            $fail('The selected ' . $attribute . ' is invalid.');
        }
    }
}