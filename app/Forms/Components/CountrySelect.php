<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Select;
use Symfony\Component\Intl\Countries;

class CountrySelect extends Select
{
    protected static array $cache = [];

    public static function make(?string $name = null): static
    {
        $self = parent::make($name ?? 'pais');

        return $self
            ->label('País')
            ->placeholder('Selecciona un país…')
            ->options(self::allEs())
            ->searchable()
            ->preload();
    }

    /** ['ES' => 'España', ...] en español (ordenado) */
    public static function allEs(): array
    {
        if (isset(self::$cache['es'])) {
            return self::$cache['es'];
        }
        $names = Countries::getNames('es');   // requiere symfony/intl
        natcasesort($names);
        return self::$cache['es'] = $names;
    }

    /** Mueve ciertos códigos al inicio en el orden dado */
    public function preferred(array $codes): static
    {
        $codes = array_map('strtoupper', $codes);
        $all = self::allEs();

        $top = array_intersect_key($all, array_flip($codes));
        $orderedTop = [];
        foreach ($codes as $c) {
            if (isset($top[$c])) $orderedTop[$c] = $top[$c];
        }

        return $this->options($orderedTop + array_diff_key($all, $orderedTop));
    }
}