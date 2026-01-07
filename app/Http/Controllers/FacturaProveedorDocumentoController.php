<?php

namespace App\Http\Controllers;

use App\Models\FacturasProveedor;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class FacturaProveedorDocumentoController extends Controller
{
    public function show(FacturasProveedor $facturaProveedor): Response
    {
        if (! $facturaProveedor->pdf_path) {
            abort(404);
        }

        $disk = Storage::disk(config('filesystems.default'));

        if (! $disk->exists($facturaProveedor->pdf_path)) {
            abort(404);
        }

        $path = $disk->path($facturaProveedor->pdf_path);
        $name = basename($facturaProveedor->pdf_path);

        return response()->download($path, $name);
    }
}
