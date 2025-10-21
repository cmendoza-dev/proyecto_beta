<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reporte - {{ $meeting->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $meeting->title }}</h1>
        <p>Fecha: {{ $meeting->date->format('d/m/Y') }}</p>
        <p>Horario: {{ $meeting->opening_time }} - {{ $meeting->closing_time }}</p>
    </div>

    <h2>Listado de Asistentes</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 60px;">DNI</th>
                <th>Nombre</th>
                <th style="width: 110px;">Teléfono</th>
                <th style="width: 160px;">Email</th>
                <th style="width: 160px;">Organización</th>
                <th style="width: 130px;">Cargo</th>
                <th style="width: 120px;">Registro</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($meeting->attendances as $a)
                @php $p = $a->participant; @endphp
                <tr>
                    <td>{{ $p->dni }}</td>
                    <td>{{ trim(($p->name ?? '') . ' ' . ($p->last_name ?? '')) }}</td>
                    <td>{{ $p->phone ?? '—' }}</td>
                    <td>{{ $p->email ?? '—' }}</td>
                    <td>{{ $p->organization ?? '—' }}</td>
                    <td>{{ $p->position ?? '—' }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($a->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{--
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>DNI</th>
                <th>Participante</th>
                <th>Hora de Ingreso</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($meeting->attendances as $index => $attendance)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $attendance->participant->dni }}</td>
                <td>{{ $attendance->participant->full_name }}</td>
                <td>{{ $attendance->registered_at->format('H:i:s') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table> --}}

    <p style="margin-top: 30px;">
        <strong>Total de asistentes:</strong> {{ $meeting->attendances->count() }}
    </p>
</body>

</html>
