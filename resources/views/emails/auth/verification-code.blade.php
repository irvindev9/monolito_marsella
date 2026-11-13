@extends('emails.layout')

@section('title', 'Código de verificación')

@section('content')
    <h2 style="margin:0 0 8px;font-size:17px;font-weight:700;color:#1c2633;">
        Verifica tu correo electrónico
    </h2>
    <p style="margin:0 0 18px;font-size:14px;line-height:1.5;color:#4b5563;">
        Hola {{ $user->name }}, ingresa el siguiente código de 4 dígitos para verificar tu cuenta:
    </p>

    <div style="margin:0 0 18px;padding:18px;background:#e4ebf2;border-left:4px solid #2A4B6D;border-radius:6px;text-align:center;">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#2A4B6D;margin-bottom:6px;">
            Código de verificación
        </div>
        <div style="font-size:32px;font-weight:700;letter-spacing:12px;color:#1c2633;font-family:monospace;">
            {{ $code }}
        </div>
    </div>

    <p style="margin:0;font-size:13px;line-height:1.5;color:#6b7280;">
        Este código expira en 15 minutos. Si no solicitaste esta cuenta, puedes ignorar este correo.
    </p>
@endsection
