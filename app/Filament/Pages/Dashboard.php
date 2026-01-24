<?php

namespace App\Filament\Pages;

use BackedEnum;
use App\Filament\Widgets\FacturasPendientesTableWidget;
use App\Filament\Widgets\IngresosGastosMesWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-home';

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function getWidgets(): array
    {
        return [
            IngresosGastosMesWidget::class,
            FacturasPendientesTableWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
