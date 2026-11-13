@extends('emails.layout')

@section('title', 'Nueva solicitud de reserva')
@section('header_badge', '#MRS-' . str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT))

@section('content')
    @php
        $requestedDate = \Carbon\Carbon::parse($reservation->reservation_date)->locale('es')->translatedFormat('l j \d\e F \d\e Y');
        $createdDate = \Carbon\Carbon::parse($reservation->created_at)->locale('es')->translatedFormat('j \d\e F \d\e Y, H:i');
        $address = trim(($reservation->house?->street?->name ?? '') . ' #' . ($reservation->house?->house_number ?? ''));
        $requester = $reservation->user?->name ?? 'Comité';
    @endphp

    <h2 style="margin:0 0 8px;font-size:17px;font-weight:700;color:#1c2633;">
        Nueva solicitud de reservación
    </h2>
    <p style="margin:0 0 18px;font-size:14px;line-height:1.5;color:#4b5563;">
        Se ha registrado una nueva solicitud de terraza pendiente de revisión.
    </p>

    <div style="margin:0 0 18px;padding:16px 18px;background:#e4ebf2;border-left:4px solid #2A4B6D;border-radius:6px;">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#2A4B6D;margin-bottom:4px;">
            Fecha solicitada
        </div>
        <div style="font-size:20px;font-weight:700;color:#1c2633;text-transform:capitalize;">
            {{ $requestedDate }}
        </div>
    </div>

    <table width="100%" cellpadding="9" cellspacing="0" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;font-size:14px;margin-bottom:22px;">
        <tr>
            <td style="color:#6b7280;width:170px;border-bottom:1px solid #eceef1;">Domicilio</td>
            <td style="font-weight:700;border-bottom:1px solid #eceef1;">{{ $address }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;border-bottom:1px solid #eceef1;">Solicitante</td>
            <td style="font-weight:700;border-bottom:1px solid #eceef1;">{{ $requester }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;border-bottom:1px solid #eceef1;">Fecha de solicitud</td>
            <td style="font-weight:700;border-bottom:1px solid #eceef1;">{{ $createdDate }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Solicitudes en el periodo</td>
            <td style="font-weight:700;">{{ $seasonRequestCount ?? 1 }}</td>
        </tr>
    </table>

    <a href="{{ url('/admin') }}"
       style="display:inline-block;padding:10px 18px;background:#2A4B6D;color:#ffffff;text-decoration:none;border-radius:6px;font-size:14px;font-weight:700;">
        Revisar en Administración
    </a>
@endsection
