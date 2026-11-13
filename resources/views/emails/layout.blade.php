<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name', 'Marsella'))</title>
</head>
<body style="margin:0;padding:24px 12px;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1c2633;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
        <tr>
            <td style="padding:20px 24px;background:#1c2633;color:#ffffff;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="font-size:18px;font-weight:700;color:#ffffff;">
                            {{ config('app.name', 'Marsella') }}
                        </td>
                        @hasSection('header_badge')
                            <td align="right" style="font-size:12px;color:#cbd5e1;">
                                @yield('header_badge')
                            </td>
                        @endif
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td style="padding:24px;">
                @yield('content')
            </td>
        </tr>

        <tr>
            <td style="padding:14px 24px;background:#f9fafb;border-top:1px solid #e5e7eb;font-size:12px;color:#6b7280;">
                Este correo fue generado automáticamente por {{ config('app.name', 'Marsella') }}.
            </td>
        </tr>
    </table>
</body>
</html>
