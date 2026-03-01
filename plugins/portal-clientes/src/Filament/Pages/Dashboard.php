<?php

namespace PortalClientes\Filament\Pages;

use App\Models\Factura;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;
    protected static ?string $navigationLabel = 'Inicio';
    protected static ?string $title = 'Panel de cliente';
    protected static ?string $slug = 'dashboard';
    protected static ?int $navigationSort = 1;

    public function getView(): string
    {
        return 'portal-clientes::filament.pages.dashboard';
    }

    public int $facturasPendientes = 0;
    public int $facturasTotal = 0;
    public float $importePendiente = 0.0;
    public float $importeTotal = 0.0;
    public int $presupuestosActivos = 0;
    public string $clienteNombre = '';

    public function mount(): void
    {
        $user = Auth::user();
        $cliente = $user?->cliente;

        if (!$cliente) {
            return;
        }

        $this->clienteNombre = $cliente->nombre ?? $user->name;

        $facturas = Factura::where('cliente_id', $cliente->id)
            ->whereIn('estado', ['emitida', 'cobrada', 'anulada'])
            ->get();

        $this->facturasTotal = $facturas->count();
        $this->importeTotal = (float) $facturas->sum('total');

        $pendientes = $facturas->where('estado', 'emitida');
        $this->facturasPendientes = $pendientes->count();
        $this->importePendiente = (float) $pendientes->sum('total');

        if (class_exists(\Presupuestos\Models\Presupuesto::class)) {
            $this->presupuestosActivos = \Presupuestos\Models\Presupuesto::where('cliente_id', $cliente->id)
                ->whereIn('estado', ['emitido', 'aceptado', 'no-aceptado', 'facturado'])
                ->count();
        }
    }
}
