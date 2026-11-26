@extends('emails.layout')

@section('title', 'Recordatorio de pago')
@section('header_badge', 'Aviso de vencimiento')

@section('content')
    <h2 style="margin:0 0 8px;font-size:17px;font-weight:700;color:#1c2633;">
        ¡Hola, vecino(a)!
    </h2>
    <p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#4b5563;">
        Esperamos que te encuentres muy bien. Te escribimos amablemente para recordarte que tienes una solicitud de reservación pendiente de pago y el plazo para cubrir la cuota está próximo a vencer.
    </p>

    <div style="margin:0 0 18px;padding:16px 18px;background:#fef9c3;border-left:4px solid #ca8a04;border-radius:6px;">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#a16207;margin-bottom:4px;">
            Importante
        </div>
        <div style="font-size:14px;line-height:1.5;color:#1c2633;">
            Para conservar tu fecha y evitar que la reservación sea liberada automáticamente al cumplirse el plazo, te invitamos a realizar el pago de la cuota correspondiente a la brevedad.
        </div>
    </div>

    <p style="margin:0 0 16px;font-size:14px;line-height:1.6;color:#4b5563;">
        Puedes consultar los datos de transferencia directamente en la plataforma dentro de la sección <strong>Mis reservas</strong>.
    </p>

    <p style="margin:0 0 22px;font-size:13px;line-height:1.5;color:#6b7280;">
        Si ya realizaste tu pago, por favor asegúrate de haber compartido tu comprobante con la administración y haz caso omiso de este mensaje. ¡Muchas gracias por tu atención!
    </p>

    <a href="{{ url('/dashboard') }}"
       style="display:inline-block;padding:10px 18px;background:#2A4B6D;color:#ffffff;text-decoration:none;border-radius:6px;font-size:14px;font-weight:700;">
        Ver mis reservas
    </a>
@endsection
