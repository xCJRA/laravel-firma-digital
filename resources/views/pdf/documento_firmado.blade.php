<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1E293B;
            padding: 40px;
        }

        .header {
            border-bottom: 3px solid #2563EB;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 20px;
            color: #1E293B;
            margin-bottom: 4px;
        }

        .header p {
            color: #64748B;
            font-size: 11px;
        }

        .badge {
            display: inline-block;
            background-color: #16A34A;
            color: white;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            margin-top: 8px;
        }

        .info-box {
            background-color: #F1F5F9;
            border-left: 4px solid #2563EB;
            padding: 12px 16px;
            margin-bottom: 24px;
            border-radius: 0 4px 4px 0;
        }

        .info-box p {
            margin-bottom: 4px;
            font-size: 11px;
            color: #475569;
        }

        .info-box p strong {
            color: #1E293B;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1E293B;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 1px solid #E2E8F0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            font-size: 11px;
        }

        thead tr {
            background-color: #1E293B;
            color: white;
        }

        thead th {
            padding: 10px 12px;
            text-align: left;
            font-weight: bold;
        }

        tbody tr:nth-child(even) {
            background-color: #F8FAFC;
        }

        tbody tr:nth-child(odd) {
            background-color: #FFFFFF;
        }

        tbody td {
            padding: 9px 12px;
            border-bottom: 1px solid #E2E8F0;
            color: #334155;
        }

        .estado-firmado {
            background-color: #DCFCE7;
            color: #15803D;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }

        .footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #E2E8F0;
            font-size: 10px;
            color: #94A3B8;
            text-align: center;
        }

        .hash-box {
            background-color: #0F172A;
            color: #A5F3FC;
            font-family: monospace;
            font-size: 9px;
            padding: 10px 14px;
            border-radius: 4px;
            margin-bottom: 24px;
            word-break: break-all;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ $documento->nombre }}</h1>
        <p>Documento firmado electrónicamente</p>
        <span class="badge">✓ COMPLETADO</span>
    </div>

    <div class="info-box">
        <p><strong>ID del documento:</strong> #{{ $documento->id }}</p>
        <p><strong>Descripción:</strong> {{ $documento->descripcion ?? 'Sin descripción' }}</p>
        <p><strong>Fecha de creación:</strong> {{ $documento->created_at->format('d/m/Y H:i') }} UTC</p>
        <p><strong>Fecha de completado:</strong> {{ now()->format('d/m/Y H:i') }} UTC</p>
        <p><strong>Total de firmantes:</strong> {{ $documento->firmantes->count() }}</p>
    </div>

    <p class="section-title">Registro de firmas</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Firmante</th>
                <th>Email</th>
                <th>Fecha y hora</th>
                <th>IP de origen</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($documento->firmantes->sortBy('orden') as $firmante)
            <tr>
                <td>{{ $firmante->orden }}</td>
                <td>{{ $firmante->nombre }}</td>
                <td>{{ $firmante->email }}</td>
                <td>
                    @if($firmante->firma)
                        {{ $firmante->firma->firmado_at->format('d/m/Y H:i:s') }} UTC
                    @else
                        —
                    @endif
                </td>
                <td>
                    @if($firmante->firma)
                        {{ $firmante->firma->ip_address }}
                    @else
                        —
                    @endif
                </td>
                <td><span class="estado-firmado">FIRMADO</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p class="section-title">Identificador del documento</p>
    <div class="hash-box">DOC-{{ $documento->id }}-{{ strtoupper(substr(md5($documento->id . $documento->created_at), 0, 32)) }}</div>

    <div class="footer">
        <p>Este documento fue firmado electrónicamente a través del sistema laravel-firma-digital.</p>
        <p>Cada firma incluye timestamp UTC e IP de origen como evidencia del acto de firma.</p>
    </div>

</body>
</html>
