<?php

namespace PortalClientes\Http\Controllers;

use App\Models\Emisor;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PortalFacturaPdfController
{
    public function show(Request $request, Factura $factura)
    {
        $clienteId = Auth::user()?->cliente?->id;

        // Ensure the factura belongs to this client and is not a draft
        if ($factura->cliente_id !== $clienteId || $factura->estado === 'borrador') {
            abort(403, 'No tienes permiso para ver esta factura.');
        }

        $factura->load(['cliente', 'serie', 'lineas.impuesto']);

        $emisor = Emisor::activo()->first();

        $PresupuestoId = DB::table('presupuestos_facturas')
            ->where('factura_id', $factura->id)
            ->value('presupuesto_id');

        if (!empty($PresupuestoId)) {
            $PresupuestoId = DB::table('presupuestos')
                ->where('id', $PresupuestoId)
                ->value('numero_completo');
        }

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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.factura', compact('factura', 'emisor', 'logo', 'PresupuestoId'))
            ->setPaper('a4');
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $font = $dompdf->getFontMetrics()->get_font('DejaVu Sans', 'normal');
        $dompdf->getCanvas()->page_text(520, 800, "{PAGE_NUM} / {PAGE_COUNT}", $font, 8, [0, 0, 0]);

        $filename = 'Factura_' . (str_replace('/', '-', $factura->numero_completo ?? $factura->id)) . '.pdf';

        return $pdf->download($filename);
    }
}
