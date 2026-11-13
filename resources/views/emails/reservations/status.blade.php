@extends('emails.layout')

@section('title', $type === 'approved' ? 'Reservación aprobada' : 'Reservación rechazada')
@section('header_badge', '#MRS-' . str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT))

@section('content')
    @php
        $requestedDate = \Carbon\Carbon::parse($reservation->reservation_date)->locale('es')->translatedFormat('l j \d\e F \d\e Y');
        $address = trim(($reservation->house?->street?->name ?? '') . ' #' . ($reservation->house?->house_number ?? ''));
        $requester = $reservation->user?->name ?? 'Comité';
        $isApproved = $type === 'approved';
    @endphp

    <h2 style="margin:0 0 8px;font-size:17px;font-weight:700;color:{{ $isApproved ? '#166534' : '#991b1b' }};">
        {{ $isApproved ? 'Reservación aprobada' : 'Reservación rechazada' }}
    </h2>
    <p style="margin:0 0 18px;font-size:14px;line-height:1.5;color:#4b5563;">
        {{ $isApproved
            ? 'Tu solicitud de reservación ha sido aprobada. Los accesos estarán disponibles el día de tu evento.'
            : 'Lamentamos informarte que tu solicitud de reservación no fue aprobada.' }}
    </p>

    <div style="margin:0 0 18px;padding:16px 18px;background:{{ $isApproved ? '#eaf1ea' : '#fbece5' }};border-left:4px solid {{ $isApproved ? '#3a5c3e' : '#a84230' }};border-radius:6px;">
        <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:{{ $isApproved ? '#3a5c3e' : '#a84230' }};margin-bottom:4px;">
            {{ $isApproved ? 'Fecha confirmada' : 'Fecha solicitada' }}
        </div>
        <div style="font-size:20px;font-weight:700;color:#1c2633;text-transform:capitalize;">
            {{ $requestedDate }}
        </div>
    </div>

    <table width="100%" cellpadding="9" cellspacing="0" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:6px;font-size:14px;margin-bottom:22px;">
        <tr>
            <td style="color:#6b7280;width:130px;border-bottom:1px solid #eceef1;">Estado</td>
            <td style="font-weight:700;border-bottom:1px solid #eceef1;color:{{ $isApproved ? '#166534' : '#991b1b' }};">
                {{ $isApproved ? 'Confirmada' : 'Rechazada' }}
            </td>
        </tr>
        <tr>
            <td style="color:#6b7280;border-bottom:1px solid #eceef1;">Domicilio</td>
            <td style="font-weight:700;border-bottom:1px solid #eceef1;">{{ $address }}</td>
        </tr>
        <tr>
            <td style="color:#6b7280;">Solicitante</td>
            <td style="font-weight:700;">{{ $requester }}</td>
        </tr>
    </table>

    <a href="{{ url('/dashboard') }}"
       style="display:inline-block;padding:10px 18px;background:#2A4B6D;color:#ffffff;text-decoration:none;border-radius:6px;font-size:14px;font-weight:700;">
        Ver mis reservas
    </a>
@endsection
