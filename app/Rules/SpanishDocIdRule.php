<?php

namespace App\Rules;

use App\Support\SpanishDocId;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SpanishDocIdRule implements ValidationRule
{
    public function __construct(
        private bool $allowNif = true,
        private bool $allowNie = true,
        private bool $allowCif = true,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $v = SpanishDocId::normalize((string) $value);

        $ok = false;
        if ($this->allowNif && SpanishDocId::isValidNif($v)) $ok = true;
        if ($this->allowNie && SpanishDocId::isValidNie($v)) $ok = true;
        if ($this->allowCif && SpanishDocId::isValidCif($v)) $ok = true;

        if (! $ok) {
            $fail('El :attribute no es un NIF/NIE/CIF válido.');
        }
    }
}