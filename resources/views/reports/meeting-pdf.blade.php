<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Reporte de Reunión</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #111;
        }

        /* Header/banner */
        .header {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 12px;
        }

        .header .col {
            display: table-cell;
            vertical-align: middle;
        }

        .header .logo-cell {
            width: 220px;
        }

        .header .logo-box {
            height: 60px;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 11px;
        }

        .header .logo {
            max-height: 60px;
            max-width: 200px;
        }

        .header .org-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .header .org-sub {
            color: #666;
            font-size: 12px;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 6px;
        }

        h2 {
            font-size: 14px;
            margin: 16px 0 8px;
        }

        .muted {
            color: #666;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .mb-3 {
            margin-bottom: 12px;
        }

        .grid {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .col {
            display: table-cell;
            vertical-align: top;
        }

        .col-2 {
            width: 50%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
        }

        th {
            background: #f5f5f5;
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 11px;
        }

        .badge-open {
            background: #e6f4ea;
            color: #1e7e34;
        }

        .badge-closed {
            background: #eee;
            color: #444;
        }

        .badge-draft {
            background: #fff7e6;
            color: #8a6d3b;
        }

        .signature-cell {
            height: 50px;
            vertical-align: bottom;
            text-align: center;
            border-bottom: 1px solid #333;
        }
    </style>
</head>

<body>
    <!-- Header/Banner del organizador -->
    <div class="header">
        <div class="col logo-cell">
            @if (!empty($organizerLogo ?? null))
            <img class="logo" src="{{ $organizerLogo }}" alt="Organizador">
            @else
            <div class="logo-box">Espacio para banner del organizador</div>
            @endif
        </div>
        {{-- <div class="col">
            <div class="org-name">
                {{ $organizerName ?? ($meeting->creator->name ?? config('app.name')) }}
            </div>
            <div class="org-sub">Organizador del evento</div>
        </div> --}}
    </div>

    <!-- Título y contenido -->
    <h1>Reporte de Reunión</h1>
    <div class="mb-3 muted">Generado: {{ now()->format('d/m/Y H:i') }}</div>

    <div class="grid mb-3">
        <div class="col col-2">
            <h2>Datos de la reunión</h2>
            <div class="mb-2"><strong>Título:</strong> {{ $meeting->title }}</div>
            <div class="mb-2"><strong>Fecha:</strong>
                {{ \Illuminate\Support\Carbon::parse($meeting->date)->format('d/m/Y') }}</div>
            <div class="mb-2"><strong>Horario:</strong>
                {{ \Carbon\Carbon::parse($meeting->opening_time)->format('h:i A') }} -
                {{ \Carbon\Carbon::parse($meeting->closing_time)->format('h:i A') }}
            </div>
            <div class="mb-2"><strong>Ubicación:</strong> {{ $meeting->location ?? '—' }}</div>
            <div class="mb-2">
                <strong>Estado:</strong>
                <span
                    class="badge
                    {{ $meeting->status === 'open' ? 'badge-open' : ($meeting->status === 'closed' ? 'badge-closed' : 'badge-draft') }}">
                   {{ $meeting->status_label }}
                </span>
            </div>
        </div>
        <div class="col col-2">
            <h2>Resumen</h2>
            <div class="mb-2"><strong>Total asistencias:</strong> {{ $meeting->attendances->count() }}</div>
            @if (!empty($meeting->description))
                <div class="mb-2"><strong>Descripción:</strong> {{ $meeting->description }}</div>
            @endif
        </div>
    </div>

    <h2>Listado de Asistentes</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 50px;">N°</th>
                <th style="width: 70px;">DNI</th>
                <th>Nombre</th>
                {{-- <th style="width: 90px;">Teléfono</th> --}}
                <th style="width: 100px;">Registro</th>
                <th style="width: 150px;">Firma</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($meeting->attendances as $index => $a)
                @php $p = $a->participant; @endphp
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $p->dni }}</td>
                    <td>{{ trim(($p->name ?? '') . ' ' . ($p->last_name ?? '')) }}</td>
                    {{-- <td>{{ $p->phone ?? '—' }}</td> --}}
                    <td>{{ \Illuminate\Support\Carbon::parse($a->created_at)->timezone('America/Lima')->format('d/m/Y h:i A') }}</td>
                    <td class="signature-cell">&nbsp;</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
