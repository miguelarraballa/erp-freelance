<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @font-face {
        font-family: 'DejaVu Sans';
        font-style: normal;
        font-weight: normal;
        src: url('{{ storage_path("fonts/dejavu-sans/DejaVuSans.ttf") }}');
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 9pt;
        color: #1a1a1a;
        line-height: 1.55;
    }

    .page {
        padding: 2cm 2cm;
    }

    /* Cabecera */
    .header {
        display: table;
        width: 100%;
        margin-bottom: 16pt;
        border-bottom: 2px solid #1a1a1a;
        padding-bottom: 10pt;
    }
    .header-logo { display: table-cell; width: 30%; vertical-align: middle; }
    .header-logo img { max-height: 45pt; max-width: 130pt; }
    .header-title {
        display: table-cell;
        vertical-align: middle;
        text-align: right;
    }
    .header-title h1 {
        font-size: 12pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5pt;
        margin-bottom: 3pt;
    }
    .header-title p { font-size: 8pt; color: #555; }

    /* Sección "Reunidos" */
    .reunidos {
        margin-bottom: 14pt;
    }
    .reunidos h2 {
        font-size: 10pt;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5pt;
        margin-bottom: 8pt;
        border-bottom: 1px solid #ccc;
        padding-bottom: 3pt;
    }
    .reunidos p { margin-bottom: 8pt; text-align: justify; }

    /* Cláusulas */
    .clausula {
        margin-bottom: 12pt;
    }
    .clausula h3 {
        font-size: 9.5pt;
        font-weight: bold;
        margin-bottom: 5pt;
    }
    .clausula p {
        text-align: justify;
        margin-bottom: 5pt;
    }
    .clausula ul {
        margin: 5pt 0 5pt 18pt;
    }
    .clausula ul li {
        margin-bottom: 3pt;
    }

    /* Separador */
    .separator {
        border: none;
        border-top: 1px solid #ccc;
        margin: 10pt 0;
    }

    /* Firmas */
    .firmas {
        margin-top: 20pt;
    }
    .firmas-table {
        display: table;
        width: 100%;
    }
    .firma-col {
        display: table-cell;
        width: 48%;
        vertical-align: top;
    }
    .firma-sep { display: table-cell; width: 4%; }
    .firma-col h4 {
        font-size: 9pt;
        font-weight: bold;
        text-transform: uppercase;
        border-bottom: 1px solid #1a1a1a;
        padding-bottom: 3pt;
        margin-bottom: 6pt;
    }
    .firma-col p { font-size: 8.5pt; margin-bottom: 3pt; }
    .firma-espacio {
        border-bottom: 1px solid #aaa;
        height: 40pt;
        margin-bottom: 8pt;
    }

    .acuerdan {
        margin-bottom: 14pt;
        font-weight: bold;
        text-align: center;
        font-size: 9pt;
    }
</style>
</head>
<body>
<div class="page">

    {{-- CABECERA --}}
    <div class="header">
        <div class="header-logo">
            @if($logo)
                <img src="{{ $logo }}" alt="Logo">
            @endif
        </div>
        <div class="header-title">
            <h1>Anexo de Encargo de Tratamiento<br>de Datos Personales</h1>
            @if($anexoRgpd->fecha_inicio)
                <p>Fecha de inicio: {{ $anexoRgpd->fecha_inicio->format('d/m/Y') }}</p>
            @endif
        </div>
    </div>

    {{-- REUNIDOS --}}
    <div class="reunidos">
        <h2>Reunidos</h2>
        <p>
            De una parte, D./Dña. <strong>{{ $anexoRgpd->cliente_nombre }}</strong>,
            con NIF/CIF <strong>{{ $anexoRgpd->cliente_nif }}</strong>,
            con domicilio en {{ $anexoRgpd->cliente_direccion }},
            y correo electrónico {{ $anexoRgpd->cliente_email }},
            actuando como representante y, en su caso, como titular y responsable de la web,
            plataforma o sistema objeto del servicio (en adelante, el <strong>Responsable del Tratamiento</strong>).
        </p>

        @php
            $emisorDir = collect(array_filter([
                $emisor?->direccion,
                trim(($emisor?->cp ? $emisor->cp . ' ' : '') . ($emisor?->ciudad ?? '')),
                trim(($emisor?->provincia ? $emisor->provincia . ' ' : '') . ($emisor?->pais ?? '')),
            ]))->implode(', ');
        @endphp

        <p>
            Y de otra, D./Dña. <strong>{{ $emisor?->nombre }}</strong>,
            con NIF/CIF <strong>{{ $emisor?->nif }}</strong>,
            con domicilio en {{ $emisorDir }},
            correo electrónico {{ $emisor?->email }},
            teléfono {{ $emisor?->telefono }}
            @if($emisor?->nombre_comercial), y nombre comercial <strong>{{ $emisor->nombre_comercial }}</strong>@endif
            (en adelante, el <strong>Encargado del Tratamiento</strong>).
        </p>

        <p>
            Ambas partes, reconociéndose capacidad legal suficiente,
        </p>
    </div>

    <p class="acuerdan">
        ACUERDAN suscribir el presente Contrato de Encargo de Tratamiento, de conformidad con lo dispuesto en
        el Reglamento (UE) 2016/679, General de Protección de Datos, y demás normativa aplicable.
    </p>

    <hr class="separator">

    {{-- CLÁUSULA 1 --}}
    <div class="clausula">
        <h3>1. Objeto</h3>
        <p>
            El presente contrato tiene por objeto regular el acceso y tratamiento de datos personales
            por parte del Encargado, únicamente en la medida necesaria para la prestación de servicios de
            <strong>{{ $anexoRgpd->descripcion_servicio }}</strong>.
        </p>
        <p>
            En concreto, el Encargado podrá acceder a sistemas, aplicaciones y entornos técnicos del Responsable
            para realizar tareas de desarrollo, configuración, mantenimiento, soporte técnico y puesta en
            funcionamiento de funcionalidades o servicios relacionados con el encargo profesional.
        </p>
    </div>

    <hr class="separator">

    {{-- CLÁUSULA 2 --}}
    <div class="clausula">
        <h3>2. Duración</h3>
        <p>
            El presente encargo tendrá una duración de <strong>{{ $anexoRgpd->duracion_acceso }}</strong>,
            con fecha de inicio el <strong>{{ $anexoRgpd->fecha_inicio?->format('d/m/Y') }}</strong>.
        </p>
        <p>
            Finalizado dicho plazo, el acceso del Encargado a los sistemas y datos del Responsable deberá cesar,
            salvo acuerdo expreso de prórroga entre las partes.
        </p>
    </div>

    <hr class="separator">

    {{-- CLÁUSULA 3 --}}
    <div class="clausula">
        <h3>3. Naturaleza y finalidad del tratamiento</h3>
        <p>
            El tratamiento tendrá carácter meramente instrumental y accesorio respecto del servicio contratado,
            y se limitará a las operaciones necesarias para la correcta ejecución del encargo profesional.
        </p>
        <p>
            En función del servicio contratado, el Encargado podrá realizar tareas de acceso, administración,
            configuración, revisión, integración, mantenimiento, soporte técnico, pruebas, correcciones o
            desarrollos sobre los sistemas autorizados por el Responsable.
        </p>
        <p>El Encargado no tratará los datos para fines propios ni distintos de los aquí indicados.</p>
    </div>

    <hr class="separator">

    {{-- CLÁUSULA 4 --}}
    <div class="clausula">
        <h3>4. Categorías de datos y de interesados</h3>
        <p>
            Con motivo de la prestación del servicio, el Encargado podrá tener acceso a las siguientes
            categorías de datos personales:
        </p>
        <ul>
            <li>datos identificativos;</li>
            <li>datos de contacto;</li>
            <li>datos introducidos en formularios web;</li>
            <li>datos de clientes y potenciales clientes;</li>
            <li>datos asociados a pedidos, reservas, compras o solicitudes;</li>
            <li>cualesquiera otros datos personales que resulten accesibles de forma incidental o necesaria
                para la prestación del servicio.</li>
        </ul>
        <p>Los colectivos o categorías de interesados afectados podrán ser, entre otros:</p>
        <ul>
            <li>usuarios del sitio web o plataforma;</li>
            <li>clientes;</li>
            <li>potenciales clientes;</li>
            <li>personas que contacten mediante formularios;</li>
            <li>compradores, solicitantes o usuarios de los servicios del Responsable.</li>
        </ul>
    </div>

    <hr class="separator">

    {{-- CLÁUSULA 5 --}}
    <div class="clausula">
        <h3>5. Sistemas y accesos autorizados</h3>
        <p>
            El Responsable autoriza al Encargado a acceder, exclusivamente para la prestación del servicio
            contratado, a los siguientes entornos o sistemas:
        </p>
        @php
            $accesos     = (array) ($anexoRgpd->accesos ?? []);
            $labels      = \AnexoRgpd\Models\AnexoRgpd::accesoLabels();
            $seleccionados = array_intersect_key($labels, array_flip($accesos));
        @endphp
        @if(count($seleccionados))
            <ul>
                @foreach($seleccionados as $key => $label)
                    <li>
                        {{ $label }}
                        @if($key === 'otros' && $anexoRgpd->accesos_otros)
                            : {{ $anexoRgpd->accesos_otros }}
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <p><em>(No se han indicado accesos específicos.)</em></p>
        @endif
        <p style="margin-top:5pt;">
            El acceso a otros sistemas o servicios distintos de los anteriores requerirá autorización
            expresa del Responsable.
        </p>
    </div>

    <hr class="separator">

    {{-- CLÁUSULA 6 --}}
    <div class="clausula">
        <h3>6. Obligaciones del Encargado del Tratamiento</h3>
        <p>El Encargado se obliga a:</p>
        <ul>
            <li>tratar los datos personales únicamente siguiendo instrucciones documentadas del Responsable;</li>
            <li>utilizar los datos personales solo para la correcta prestación del servicio contratado;</li>
            <li>no destinar, aplicar ni utilizar los datos con una finalidad distinta;</li>
            <li>mantener el deber de secreto y confidencialidad respecto de los datos personales a los que
                acceda, incluso después de finalizar la prestación del servicio;</li>
            <li>garantizar que las personas autorizadas para tratar datos se hayan comprometido a respetar
                la confidencialidad o estén sujetas a una obligación legal de confidencialidad;</li>
            <li>aplicar medidas técnicas y organizativas apropiadas para garantizar un nivel de seguridad
                adecuado al riesgo;</li>
            <li>no comunicar ni ceder los datos a terceros, salvo obligación legal o autorización expresa
                y previa del Responsable;</li>
            <li>no subcontratar con terceros ninguna de las prestaciones que impliquen tratamiento de datos
                personales objeto de este contrato;</li>
            <li>informar al Responsable, sin dilación indebida, de cualquier incidente de seguridad o
                violación de seguridad de los datos personales de la que tenga conocimiento;</li>
            <li>asistir al Responsable, en la medida de lo posible y atendiendo a la naturaleza del
                tratamiento, para que este pueda cumplir con sus obligaciones legales en materia de
                protección de datos.</li>
        </ul>
    </div>

    <hr class="separator">

    {{-- CLÁUSULA 7 --}}
    <div class="clausula">
        <h3>7. Obligaciones del Responsable del Tratamiento</h3>
        <p>Corresponde al Responsable:</p>
        <ul>
            <li>facilitar al Encargado únicamente los accesos y datos necesarios para la prestación del servicio;</li>
            <li>impartir las instrucciones necesarias para el tratamiento;</li>
            <li>velar, antes y durante todo el tratamiento, por el cumplimiento de la normativa de protección
                de datos;</li>
            <li>garantizar que dispone de legitimación suficiente para el tratamiento de los datos personales
                alojados o tratados a través de sus sistemas, web o servicios asociados.</li>
        </ul>
    </div>

    <hr class="separator">

    {{-- CLÁUSULA 8 --}}
    <div class="clausula">
        <h3>8. Medidas de seguridad</h3>
        <p>
            El Encargado adoptará las medidas técnicas y organizativas razonables y apropiadas para proteger
            la confidencialidad, integridad, disponibilidad y resiliencia de los datos personales, atendiendo
            al estado de la técnica, la naturaleza de los datos y los riesgos del tratamiento.
        </p>
        <p>Entre otras, podrá aplicar medidas como:</p>
        <ul>
            <li>control de acceso mediante credenciales individuales;</li>
            <li>uso de contraseñas robustas;</li>
            <li>limitación de accesos a los datos estrictamente necesarios;</li>
            <li>custodia segura de las credenciales recibidas;</li>
            <li>eliminación o devolución de accesos una vez finalizado el servicio.</li>
        </ul>
    </div>

    <hr class="separator">

    {{-- CLÁUSULA 9 --}}
    <div class="clausula">
        <h3>9. Violaciones de seguridad</h3>
        <p>
            En caso de que el Encargado tenga conocimiento de una violación de seguridad que afecte a datos
            personales objeto de este encargo, lo notificará al Responsable sin dilación indebida, facilitando
            toda la información disponible y razonablemente necesaria para su análisis y gestión.
        </p>
    </div>

    <hr class="separator">

    {{-- CLÁUSULA 10 --}}
    <div class="clausula">
        <h3>10. Subcontratación</h3>
        <p>
            Queda expresamente establecido que el Encargado no podrá subcontratar con terceros ninguna actividad
            que implique acceso o tratamiento de los datos personales objeto del presente contrato, salvo
            autorización previa, expresa y por escrito del Responsable.
        </p>
    </div>

    <hr class="separator">

    {{-- CLÁUSULA 11 --}}
    <div class="clausula">
        <h3>11. Destino de los datos al finalizar el servicio</h3>
        <p>Una vez finalice la prestación del servicio, el Encargado deberá:</p>
        <ul>
            <li>cesar en el acceso a los sistemas del Responsable;</li>
            <li>devolver o suprimir, a elección del Responsable, los datos personales a los que hubiera tenido
                acceso, así como cualquier soporte o copia que los contenga, salvo que exista una obligación
                legal de conservación;</li>
            <li>eliminar las credenciales o accesos facilitados, o solicitar al Responsable su sustitución
                o revocación.</li>
        </ul>
        <p>
            No obstante, el Encargado podrá conservar únicamente la información mínima imprescindible, debidamente
            bloqueada, cuando sea necesaria para atender posibles responsabilidades legales derivadas de la
            prestación del servicio.
        </p>
    </div>

    <hr class="separator">

    {{-- CLÁUSULA 12 --}}
    <div class="clausula">
        <h3>12. Confidencialidad</h3>
        <p>
            El Encargado se compromete a guardar la máxima reserva y confidencialidad sobre toda la información
            y datos personales a los que acceda con motivo de la prestación del servicio, obligándose a no
            divulgarlos, transmitirlos o ponerlos a disposición de terceros, salvo obligación legal o
            autorización expresa del Responsable.
        </p>
    </div>

    <hr class="separator">

    {{-- CLÁUSULA 13 --}}
    <div class="clausula">
        <h3>13. Responsabilidad</h3>
        <p>
            Cada parte responderá de las obligaciones que legalmente le correspondan conforme a la normativa
            de protección de datos.
        </p>
        <p>
            El Encargado responderá únicamente por los daños y perjuicios derivados del incumplimiento de las
            obligaciones que le correspondan como encargado del tratamiento o de las instrucciones lícitas
            recibidas del Responsable.
        </p>
    </div>

    @if($anexoRgpd->observaciones)
    <hr class="separator">

    {{-- CLÁUSULA 14 --}}
    <div class="clausula">
        <h3>14. Observaciones</h3>
        <p>{{ $anexoRgpd->observaciones }}</p>
    </div>
    @endif

    <hr class="separator">

    {{-- CLÁUSULA 15 (o 14 si no hay observaciones) --}}
    <div class="clausula">
        <h3>{{ $anexoRgpd->observaciones ? '15' : '14' }}. Legislación aplicable y fuero</h3>
        <p>
            El presente contrato se regirá por la normativa española y europea en materia de protección
            de datos.
        </p>
        <p>
            Para la resolución de cualquier controversia derivada de su interpretación o ejecución, las
            partes se someten expresamente a los Juzgados y Tribunales de Málaga, con renuncia a cualquier
            otro fuero que pudiera corresponderles, salvo que la normativa aplicable disponga otra cosa.
        </p>
    </div>

    <hr class="separator">

    {{-- FIRMAS --}}
    <p style="margin-bottom:12pt; text-align:justify;">
        Y en prueba de conformidad, ambas partes firman el presente documento, por duplicado y a un solo
        efecto, en la fecha que figure en la firma.
    </p>

    <p style="margin-bottom:18pt;">
        En ______________________, a _____ de ______________________ de ________
    </p>

    <div class="firmas">
        <div class="firmas-table">
            <div class="firma-col">
                <h4>El Responsable del Tratamiento</h4>
                <div class="firma-espacio"></div>
                <p>Fdo.: <strong>{{ $anexoRgpd->cliente_firmante ?: $anexoRgpd->cliente_nombre }}</strong></p>
                <p>NIF/CIF: {{ $anexoRgpd->cliente_nif }}</p>
                @if($anexoRgpd->cliente_cargo)
                    <p>Cargo: {{ $anexoRgpd->cliente_cargo }}</p>
                @else
                    <p>Cargo: representante</p>
                @endif
            </div>
            <div class="firma-sep"></div>
            <div class="firma-col">
                <h4>El Encargado del Tratamiento</h4>
                <div class="firma-espacio"></div>
                <p>Fdo.: <strong>{{ $emisor?->nombre }}</strong></p>
                <p>NIF/CIF: {{ $emisor?->nif }}</p>
                @if($emisor?->nombre_comercial)
                    <p>{{ $emisor->nombre_comercial }}</p>
                @endif
            </div>
        </div>
    </div>

</div>
</body>
</html>
