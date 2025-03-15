<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Lang;

class GamePasswordRule implements ValidationRule
{
    private $gamePassword;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($gamePassword)
    {
        $this->gamePassword = $gamePassword;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ((int) $value !== $this->gamePassword) {
            $fail(Lang::get('Invalid pin code'));
        }
    }
}
