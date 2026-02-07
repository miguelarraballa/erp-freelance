<?php

namespace Presupuestos\Http\Controllers;

use Presupuestos\Models\Presupuesto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Emisor;

class PresupuestoPdfController extends Controller
{
    public function show(Request $request, Presupuesto $presupuesto)
    {
        $presupuesto->load(['cliente','serie','lineas.impuesto']);
        $emisor = Emisor::activo()->first();

        // Logo en base64 (opcional)
        $logo = null;
        if ($emisor && $emisor->logo_path) {
            $candidates = [
                public_path('storage/'.$emisor->logo_path),
                public_path($emisor->logo_path),
                storage_path('app/public/'.$emisor->logo_path),
                storage_path('app/private/'.$emisor->logo_path),
                storage_path('app/'.$emisor->logo_path),
            ];

            foreach ($candidates as $abs) {
                if (! is_file($abs)) {
                    continue;
                }

                $mime = mime_content_type($abs) ?: 'image/png';
                // Dompdf necesita el MIME correcto; el de SVG debe incluir "+xml"
                if ($mime === 'image/svg') {
                    $mime = 'image/svg+xml';
                }
                $data = file_get_contents($abs);
                $logo = 'data:'.$mime.';base64,'.base64_encode($data);
                break;
            }
        }

        config([
            'dompdf.options.defaultFont'          => 'DejaVu Sans',
            'dompdf.options.isRemoteEnabled'      => true,
            'dompdf.options.isHtml5ParserEnabled' => true,
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('presupuestos::pdf.presupuesto', compact('presupuesto','emisor','logo'))
            ->setPaper('a4');
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $font = $dompdf->getFontMetrics()->get_font('DejaVu Sans', 'normal');
        $dompdf->getCanvas()->page_text(
            520,      // x (ajusta según tus márgenes)
            800,      // y (cerca del pie, A4 en puntos)
            "{PAGE_NUM} / {PAGE_COUNT}",
            $font,
            8,
            [0, 0, 0]
        );

        $filename = 'Presupuesto_' . (str_replace('/', '-', $presupuesto->numero_completo ?? $presupuesto->id)) . '.pdf';

        return $pdf->download($filename);
    }
}
