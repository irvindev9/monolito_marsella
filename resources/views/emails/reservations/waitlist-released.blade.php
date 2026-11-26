@extends('emails.layout')

@section('title', 'Fecha disponible')
@section('header_badge', 'Lista de espera')

@section('content')
    @php
        $requestedDate = \Carbon\Carbon::parse($reservation->reservation_date)->locale('es')->translatedFormat('l j \d\e F \d\e Y');
    @endphp

    <h2 style="margin:0 0 8px;font-size:17px;font-weight:700;color:#166534;">
        ¡La fecha que esperabas se ha liberado!
    </h2>
    <p style="margin:0 0 18px;font-size:14px;line-height:1.5;color:#4b5563;">
        Te notificamos porque estabas en la lista de espera para esta fecha. La reservación anterior fue cancelada y la fecha ya se encuentra disponible para quien desee solicitarla.
    </p>

    <div style="margin:0 0 18px;padding:16px 18px;background:#eaf1ea;border-left:4px solid #3a5c3e;border-radius:6px;">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#3a5c3e;margin-bottom:4px;">
            Fecha liberada
        </div>
        <div style="font-size:20px;font-weight:700;color:#1c2633;text-transform:capitalize;">
            {{ $requestedDate }}
        </div>
    </div>

    <p style="margin:0 0 22px;font-size:13px;line-height:1.5;color:#6b7280;">
        <strong>Importante:</strong> Estar en la lista de espera no realiza la reserva automáticamente. Ingresa a la plataforma lo antes posible si deseas reservar esta fecha.
    </p>

    <a href="{{ url('/dashboard') }}"
       style="display:inline-block;padding:10px 18px;background:#2A4B6D;color:#ffffff;text-decoration:none;border-radius:6px;font-size:14px;font-weight:700;">
        Ir a reservar fecha
    </a>
@endsection
