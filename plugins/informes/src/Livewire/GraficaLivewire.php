<?php

namespace Informes\Livewire;

use Illuminate\View\View;
use Informes\Models\Grafica;
use Informes\Services\GraficaDataService;
use Livewire\Component;

/**
 * Componente Livewire que renderiza una gráfica individual.
 * Se usa como <livewire:informes-grafica :grafica-id="$grafica->id" />
 */
class GraficaLivewire extends Component
{
    public int $graficaId;

    public function render(): View
    {
        $grafica = Grafica::with('fuentes')->findOrFail($this->graficaId);
        $service = app(GraficaDataService::class);

        if ($grafica->isStat()) {
            $stats = $service->buildStatData($grafica);
            return view('informes::livewire.grafica', compact('grafica', 'stats'));
        }

        $chartOptions = $service->buildApexOptions($grafica);

        return view('informes::livewire.grafica', compact('grafica', 'chartOptions'));
    }
}
