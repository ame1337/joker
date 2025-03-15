<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class KickUserRule implements ValidationRule
{
    private $players;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($players)
    {
        $this->players = $players;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->players->has($value) || $value === auth()->user()->player->position) {
            $fail('bad :attribute value');
        }
    }
}
