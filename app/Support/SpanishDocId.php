<?php

namespace App\Support;

final class SpanishDocId {
    private const DNI_LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';
    // CIF: letras iniciales permitidas (AEAT)
    private const CIF_INITIALS = 'ABCDEFGHJNPQRSUVW';
    // CIF: grupos para tipo de control
    private const CIF_MUST_DIGIT  = ['A','B','E','H'];
    private const CIF_MUST_LETTER = ['P','Q','R','S','W','N'];

    public static function normalize(?string $value): string
    {
        $v = strtoupper((string) $value);
        // quita espacios y separadores
        return preg_replace('/[\s\-\.]/', '', $v) ?? '';
    }

    public static function validate(string $value): bool
    {
        $v = self::normalize($value);
        if ($v === '') return false;

        return self::isValidNif($v) || self::isValidNie($v) || self::isValidCif($v);
    }

    public static function isValidNif(string $v): bool
    {
        // DNI 8 dígitos + letra, y NIF especiales K/L/M (mismo algoritmo de DNI)
        if (!preg_match('/^(\d{8}|[KLM]\d{7})([A-Z])$/', $v, $m)) {
            return false;
        }
        $num = $m[1];
        // K/L/M → se considera un 0 delante para formar 8 dígitos
        if ($num[0] === 'K' || $num[0] === 'L' || $num[0] === 'M') {
            $num = '0' . substr($num, 1); // 0 + 7 dígitos
        }
        $idx = (int) $num % 23;
        $expected = self::DNI_LETTERS[$idx];
        return $m[2] === $expected;
    }

    public static function isValidNie(string $v): bool
    {
        // NIE X/Y/Z + 7 dígitos + letra
        if (!preg_match('/^[XYZ]\d{7}[A-Z]$/', $v)) {
            return false;
        }
        $map = ['X' => '0', 'Y' => '1', 'Z' => '2'];
        $num = $map[$v[0]] . substr($v, 1, 7);
        $idx = ((int) $num) % 23;
        $expected = self::DNI_LETTERS[$idx];
        return $v[-1] === $expected;
    }

    public static function isValidCif(string $v): bool
    {
        if (!preg_match('/^[' . self::CIF_INITIALS . ']\d{7}[0-9A-J]$/', $v)) {
            return false;
        }
        $letter = $v[0];
        $digits = substr($v, 1, 7);
        $ctrl   = $v[8];

        // Sumas posiciones: (1-indexed sobre $digits)
        $sumEven = 0; // 2,4,6
        $sumOdd  = 0; // 1,3,5,7  (doblar y sumar dígitos)
        for ($i = 0; $i < 7; $i++) {
            $d = (int) $digits[$i];
            if ( ($i + 1) % 2 === 0 ) {
                $sumEven += $d;
            } else {
                $x = $d * 2;
                $sumOdd += (int) floor($x / 10) + ($x % 10);
            }
        }
        $total = $sumEven + $sumOdd;
        $digit = (10 - ($total % 10)) % 10;
        $letterMap = 'JABCDEFGHI';
        $letterCtrl = $letterMap[$digit];

        // Reglas de AEAT: algunos deben llevar dígito, otros letra, otros cualquiera
        if (in_array($letter, self::CIF_MUST_DIGIT, true)) {
            return $ctrl === (string) $digit;
        }
        if (in_array($letter, self::CIF_MUST_LETTER, true)) {
            return $ctrl === $letterCtrl;
        }
        // Resto de iniciales acepta cualquiera de los dos si coincide
        return $ctrl === (string) $digit || $ctrl === $letterCtrl;
    }
}