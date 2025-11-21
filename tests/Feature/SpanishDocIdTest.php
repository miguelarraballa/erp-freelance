<?php

use App\Support\SpanishDocId;

it('normaliza NIF/NIE/CIF', function () {
    expect(SpanishDocId::normalize(' 12.345.678-z '))->toBe('12345678Z');
    expect(SpanishDocId::normalize('x-1234567-l'))->toBe('X1234567L');
});

it('valida NIF correcto', function () {
    // 12345678 % 23 = 14 → letra Z
    expect(SpanishDocId::validate('12345678Z'))->toBeTrue();
});

it('valida NIE correcto', function () {
    // X1234567 → letra L
    expect(SpanishDocId::validate('X1234567L'))->toBeTrue();
});

it('valida CIF con dígito de control', function () {
    // Para B + 1234567 → control = 4 → B12345674
    expect(SpanishDocId::validate('B12345674'))->toBeTrue();
});

it('valida CIF con letra de control', function () {
    // Para P + 1234567 → control = D → P1234567D
    expect(SpanishDocId::validate('P1234567D'))->toBeTrue();
});

it('rechaza identificadores inválidos', function () {
    expect(SpanishDocId::validate('12345678A'))->toBeFalse(); // NIF letra mal
    expect(SpanishDocId::validate('Z1234567L'))->toBeFalse(); // NIE prefijo mal
    expect(SpanishDocId::validate('B1234567A'))->toBeFalse(); // CIF control mal
});