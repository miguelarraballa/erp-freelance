@php
    $fmt = fn ($n) => number_format((float) $n, 2, ',', '.');
@endphp
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Factura {{ $factura->numero_completo ?? $factura->id }}</title>
<style>
    @page { size: A4; margin: 24mm 18mm; }
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
    h1,h2,h3 { margin: 0 0 6px 0; }
    ul {padding: 0; margin: 0; list-style: none; font-size: 10px;}
    .row { display: block; width: 100%; }
    .col-6 { width: 50%; float: left; }
    .right { text-align: right; }
    .concepto { width: 50% }
    .muted { color: #666; }
    .box { border: 1px solid #ddd; padding: 10px; border-radius: 4px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 8px; border-bottom: 1px solid #e5e5e5; vertical-align: top; font-size: 10px; }
    th { background: #f7f7f7; text-align: left; }
    tfoot td { border-top: 1px solid #aaa; font-weight: bold; }
    .totals td { padding: 6px; }
    .small { font-size: 11px; }
    .mb-2 { margin-bottom: 12px; }
    .mb-3 { margin-bottom: 18px; }
    .mb-4 { margin-bottom: 24px; }
    .clearfix::after { content: ""; display: table; clear: both; }
    #notas-legales { position: fixed; bottom: -10pt; width: 100%; font-size: 8px; text-align: justify}
    #formas-de-pago { font-size: 10px; margin-top: 24px; margin-bottom: 24px; }
</style>
</head>
<body>
    <div class="clearfix mb-4">
        
        <div class="col-6">
            @if(!empty($logo))
                <img src="{{ $logo }}" alt="Logo" style="height: 48px;" class="mb-2">
            @endif
            <div class="small">
            @if($emisor)
                    <div><strong>{{ $emisor->nombre }}</strong></div>
                    
                    @if($emisor->direccion)
                        <div>{{ $emisor->direccion }}</div>
                    @endif
                    <div>
                        {{ $emisor->cp }} {{ $emisor->ciudad }}{{ $emisor->provincia ? ', '.$emisor->provincia. ' ' . $emisor->pais  : '' }}
                    </div>
                    @if($emisor->nif)<div class="mb-2">{{ $emisor->nif }}</div>@endif
                    @if($emisor->email)<div>{{ $emisor->email }}</div>@endif
                    @if($emisor->telefono)<div>{{ $emisor->telefono }}</div>@endif
                    @if($emisor->web)<div>{{ $emisor->web }}</div>@endif
                   
                @endif
            </div>
        </div>

        <div class="col-6">
            <h2>Factura {{ $factura->numero_completo ?? 'Proforma' }}</h2>
            <div class="muted small mb-2">
                @if($factura->tipo !== 'normal')
                    <div>Tipo: {{ ucfirst($factura->tipo) }}</div>
                @endif
                <div>Fecha: {{ \Illuminate\Support\Carbon::parse($factura->fecha ?? now())->format('d/m/Y') }}</div>
                @if($factura->vencimiento)
                    <div>Vencimiento: {{ \Illuminate\Support\Carbon::parse($factura->vencimiento)->format('d/m/Y') }}</div>
                @endif
                <div>Estado: {{ ucfirst($factura->estado) }}</div>
                @if($factura->rectifica_id)
                    <div>Rectifica a: {{ optional($factura->rectifica)->numero_completo ?? $factura->rectifica_id }}</div>
                @endif
                @if(!empty($PresupuestoId))
                    <div>Presupuesto asociado: {{ $PresupuestoId }}</div>
                @endif
            </div>
            
            <div class="box small">
                {!! nl2br(e($factura->datos_facturacion ?? '')) !!}
            </div>
            
            @if($factura->hash)
                <div class="box small">
                    <div>Hash: {{ $factura->hash }}</div>
                    {{-- Aquí podrías incrustar un QR con el hash si lo necesitas --}}
                </div>
            @endif
            
        </div>
    </div>

    <div class="row clearfix mb-3">
        
    </div>

    <table class="mb-3">
        <thead>
            <tr>
                <th>Concepto</th>
                <th class="right" style="width: 5%">Cant.</th>
                <th class="right" style="width: 5%">Precio</th>
                <th class="right" style="width: 5%">Dto.</th>
                <th class="right" style="width: 10%">IVA</th>
                <th class="right" style="width: 11%">IRPF</th>
                <th class="right" style="width: 13%">Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($factura->lineas as $ln)
            <tr>
                <td>{!! nl2br(e($ln->concepto)) !!}</td>
                <td class="right">{{ $fmt($ln->cantidad) }}</td>
                <td class="right">{{ $fmt($ln->precio_unitario) }}€</td>
                <td class="right">{{ $fmt($ln->descuento_pct ?? 0) }}</td>
                <td class="right">
                    @php
                        $ivaLinea = (float) ($ln->iva_linea ?? 0);
                    @endphp
                    {{ $fmt($ivaLinea) }}€
                </td>
                <td class="right">
                    @php
                        $irpfLinea = (float) ($ln->irpf_linea ?? 0);
                    @endphp
                    {{ $irpfLinea != 0 ? '-' . $fmt(abs($irpfLinea)) : $fmt(0) }}€
                </td>
                <td class="right">{{ $fmt($ln->total_linea ?? 0) }}€</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals" style="width: 50%; margin-left: auto;">
        <tbody>
            <tr>
                <td>Base imponible</td>
                <td class="right">{{ $fmt($factura->base ?? 0) }}€</td>
            </tr>
            <tr>
                <td>IVA total</td>
                <td class="right">{{ $fmt($factura->iva_total ?? 0) }}€</td>
            </tr>
            @if(($factura->irpf_total ?? 0) != 0)
            <tr>
                <td>IRPF total</td>
                <td class="right">-{{ $fmt($factura->irpf_total) }}€</td>
            </tr>
            @endif
            <tr>
                <td>Total</td>
                <td class="right">{{ $fmt($factura->total ?? 0) }}€</td>
            </tr>
        </tbody>
    </table>
    
    <div class="clearfix"></div>

    <div id="formas-de-pago">
        Puede pagar la factura mediante: 
        <ul class="small">
            <li><b>Transferencia bancaria: </b> {{ $emisor->nombre ?? 'N/A' }} - (Triodos Bank) {{ $emisor->iban ?? 'N/A' }} </li>
            <li><b>PayPal:</b> https://www.paypal.com/ncp/payment/UXYY3EYERUTAG</li>
            <li><b>Bizum:</b> {{ $emisor->telefono ?? 'N/A' }}</li>
        </ul>
    </div>

    @if($factura->notas)
        <div class="mb-3">
            <h3>Notas:</h3>
            <div class="small">{!! nl2br(e($factura->notas)) !!}</div>
        </div>
    @endif

    @if($emisor && $emisor->pie_factura)
    <div class="mb-3">
        <div class="small">{!! nl2br(e($emisor->pie_factura)) !!}</div>
    </div>
    @endif

    @if($emisor && $emisor->notas_legales)
        <div class="mb-3" id="notas-legales">
            <div>{!! nl2br(e($emisor->notas_legales)) !!}</div>
        </div>
    @endif

</body>
</html>
