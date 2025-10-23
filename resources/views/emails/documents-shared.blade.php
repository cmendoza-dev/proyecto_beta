{{-- filepath: c:\xampp\htdocs\proyecto_beta\resources\views\emails\documents-shared.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; padding: 12px 24px; background: #4f46e5; color: white; text-decoration: none; border-radius: 6px; margin: 10px 0; }
        .file-list { list-style: none; padding: 0; }
        .file-item { background: white; padding: 15px; margin: 10px 0; border-radius: 6px; border-left: 4px solid #4f46e5; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>📄 Documentos Compartidos</h2>
        </div>
        <div class="content">
            <p><strong>{{ $senderName }}</strong> ha compartido documentos de la reunión:</p>

            <h3>{{ $meeting->title }}</h3>
            <p><strong>Fecha:</strong> {{ $meeting->date->format('d/m/Y') }}</p>

            <h4>Documentos disponibles:</h4>
            <ul class="file-list">
                @foreach($fileUrls as $index => $url)
                    <li class="file-item">
                        <strong>Documento {{ $index + 1 }}</strong>
                        <br>
                        <a href="{{ $url }}" class="button">Descargar</a>
                    </li>
                @endforeach
            </ul>

            <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">
                Este es un mensaje automático. Por favor no responda a este correo.
            </p>
        </div>
    </div>
</body>
</html>
