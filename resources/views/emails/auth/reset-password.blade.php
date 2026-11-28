@extends('emails.layout')

@section('title', 'Restablecer contraseña')

@section('content')
    <h2 style="margin:0 0 8px;font-size:17px;font-weight:700;color:#1c2633;">
        Restablece tu contraseña
    </h2>
    <p style="margin:0 0 18px;font-size:14px;line-height:1.5;color:#4b5563;">
        Hola {{ $user->name }}, recibimos una solicitud para restablecer la contraseña de tu cuenta. Haz clic en el siguiente botón para elegir una nueva contraseña:
    </p>

    <div style="margin:0 0 20px;text-align:center;">
        <a href="{{ $url }}"
           style="display:inline-block;padding:12px 22px;background:#2A4B6D;color:#ffffff;text-decoration:none;border-radius:6px;font-size:14px;font-weight:700;">
            Restablecer contraseña
        </a>
    </div>

    <div style="margin:0 0 18px;padding:14px 16px;background:#e4ebf2;border-left:4px solid #2A4B6D;border-radius:6px;font-size:13px;color:#1c2633;">
        Este enlace expirará en <strong>{{ $expireMinutes }} minutos</strong>. Si no solicitaste restablecer tu contraseña, no es necesario realizar ninguna acción.
    </div>

    <p style="margin:0;font-size:12px;line-height:1.5;color:#6b7280;word-break:break-all;">
        Si tienes problemas con el botón, copia y pega este enlace en tu navegador:<br>
        <a href="{{ $url }}" style="color:#2A4B6D;">{{ $url }}</a>
    </p>
@endsection
