<?php

namespace AnexoRgpd\Http\Controllers;

use AnexoRgpd\Models\AnexoRgpd;
use App\Models\Emisor;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AnexoRgpdPdfController extends Controller
{
    public function show(Request $request, AnexoRgpd $anexoRgpd)
    {
        $anexoRgpd->load('cliente');
        $emisor = Emisor::activo()->first();

        // Logo en base64
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
                if (!is_file($abs)) continue;

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

        $pdf = Pdf::loadView('anexo-rgpd::pdf.anexo-rgpd', compact('anexoRgpd', 'emisor', 'logo'))
            ->setPaper('a4');

        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $w      = $canvas->get_width();   // ~595 pt (A4)
        $h      = $canvas->get_height();  // ~842 pt (A4)
        $cm2    = 56.69;                  // 2 cm en puntos (72 * 2 / 2.54)

        $canvas->page_script(function ($pageNum, $pageCount, $canvas, $fontMetrics) use ($w, $h, $cm2) {
            // Franja blanca cabecera (2 cm)
            $canvas->filled_rectangle(0, 0, $w, $cm2, [1, 1, 1]);

            // Franja blanca pie (2 cm)
            $canvas->filled_rectangle(0, $h - $cm2, $w, $cm2, [1, 1, 1]);

            // Número de página centrado en el pie
            $font  = $fontMetrics->get_font('DejaVu Sans', 'normal');
            $texto = "{$pageNum} / {$pageCount}";
            $tw    = $fontMetrics->get_text_width($texto, $font, 8);
            $canvas->text(
                ($w - $tw) / 2,
                $h - ($cm2 / 2) - 4,
                $texto,
                $font,
                8,
                [0.4, 0.4, 0.4]
            );
        });

        $filename = 'AnexoRGPD_' . ($anexoRgpd->cliente->razon_social ?? $anexoRgpd->cliente_nombre ?? $anexoRgpd->id) . '.pdf';
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename) . '.pdf';

        return $pdf->download($filename);
    }
}
