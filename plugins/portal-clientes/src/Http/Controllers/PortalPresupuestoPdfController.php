<?php

namespace PortalClientes\Http\Controllers;

use App\Models\Emisor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Presupuestos\Models\Presupuesto;

class PortalPresupuestoPdfController
{
    public function show(Request $request, Presupuesto $presupuesto)
    {
        $clienteId = Auth::user()?->cliente?->id;

        if ($presupuesto->cliente_id !== $clienteId || $presupuesto->estado === 'borrador') {
            abort(403, 'No tienes permiso para ver este presupuesto.');
        }

        $presupuesto->load(['cliente', 'serie', 'lineas.impuesto']);

        $emisor = Emisor::activo()->first();

        $logo = null;
        if ($emisor && $emisor->logo_path) {
            $candidates = [
                public_path('storage/' . $emisor->logo_path),
                public_path($emisor->logo_path),
                storage_path('app/public/' . $emisor->logo_path),
                storage_path('app/private/' . $emisor->logo_path),
                storage_path('app/' . $emisor->logo_path),
            ];

            foreach ($candidates as $abs) {
                if (!is_file($abs)) {
                    continue;
                }
                $mime = mime_content_type($abs) ?: 'image/png';
                if ($mime === 'image/svg') {
                    $mime = 'image/svg+xml';
                }
                $logo = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($abs));
                break;
            }
        }

        config([
            'dompdf.options.defaultFont'          => 'DejaVu Sans',
            'dompdf.options.isRemoteEnabled'      => true,
            'dompdf.options.isHtml5ParserEnabled' => true,
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('presupuestos::pdf.presupuesto', compact('presupuesto', 'emisor', 'logo'))
            ->setPaper('a4');
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $font = $dompdf->getFontMetrics()->get_font('DejaVu Sans', 'normal');
        $dompdf->getCanvas()->page_text(520, 800, "{PAGE_NUM} / {PAGE_COUNT}", $font, 8, [0, 0, 0]);

        $filename = 'Presupuesto_' . (str_replace('/', '-', $presupuesto->numero_completo ?? $presupuesto->id)) . '.pdf';

        return $pdf->download($filename);
    }
}
