<?php

namespace Servidores\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Servidores\Filament\Resources\Servidores\ServidorResource;
use Servidores\Filament\Widgets\ServidoresRenovacionWidget;

class ServidoresPlugin implements Plugin
{
    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return 'servidores';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                ServidorResource::class,
            ])
            ->widgets([
                ServidoresRenovacionWidget::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
    }
}
