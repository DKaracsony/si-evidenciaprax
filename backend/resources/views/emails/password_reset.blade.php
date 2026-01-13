@php
    /** @var string $reset_url  Required */
    /** @var \Carbon\Carbon|string|null $expires_at  Optional: exact expiry */
    /** @var int|null $expires_in_minutes           Optional: relative expiry (e.g., 120) */

    $appName = config('app.name', 'Evidencia praxí');

    $expiryNote = 'krátko po odoslaní (max. 2 hodiny)';
    if (!empty($expires_in_minutes) && is_numeric($expires_in_minutes)) {
        $mins = (int) $expires_in_minutes;
        if ($mins % 60 === 0) {
            $hours = (int)($mins / 60);
            $expiryNote = "{$hours} " . ($hours === 1 ? 'hodinu' : ($hours < 5 ? 'hodiny' : 'hodín'));
        } else {
            $expiryNote = "{$mins} minút";
        }
    }
    if (!empty($expires_at)) {
        try {
            $dt = \Carbon\Carbon::parse($expires_at)->timezone('Europe/Bratislava');
            $expiryNote = $dt->format('d.m.Y H:i');
        } catch (\Throwable $e) {
        }
    }
@endphp

    <!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obnovenie hesla — {{ $appName }}</title>
</head>
<body style="margin:0; padding:20px; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color:#f5f5f5;">
<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width:100%;">
    <tr>
        <td align="center">
            <div style="max-width:600px; margin:0 auto; padding:30px; background-color:#ffffff; border:1px solid #ddd; box-shadow:0 4px 12px rgba(0,0,0,0.15); text-align:center;">

                <h1 style="color:#1868DB; font-size:27px; margin:0 0 20px;">
                    Obnovenie hesla
                </h1>

                <p style="margin:15px 0; color:#333; font-size:16px; line-height:1.5;">
                    Dostali sme požiadavku na obnovenie hesla k účtu v <strong>{{ $appName }}</strong>.
                    Z bezpečnostných dôvodov v tejto správe neuvádzame žiadne citlivé údaje.
                </p>

                <p style="margin:15px 0; color:#333; font-size:16px; line-height:1.5;">
                    Kliknite na tlačidlo nižšie a nastavte si nové heslo.
                </p>

                <p style="margin-top:25px;">
                    <a href="{{ $reset_url }}"
                       style="display:inline-block; padding:12px 24px; background-color:#1868DB; border:4px solid #a9c9ff; color:#ffffff; text-decoration:none; box-shadow:0 8px 18px rgba(0,0,0,0.30); font-weight:700; border-radius:12px; font-size:16px;"
                       aria-label="Otvorí stránku na obnovenie hesla">
                        Obnoviť heslo
                    </a>
                </p>

                <p style="margin:15px 0; color:#333; font-size:16px; line-height:1.5;">
                    Platnosť odkazu: <strong>{{ $expiryNote }}</strong>
                </p>

                <hr style="margin:24px 0; border:none; border-top:1px solid #eee;">

                <p style="margin:0 0 8px; color:#333; font-size:14px; line-height:1.5;">
                    Ak tlačidlo nefunguje, skopírujte tento odkaz do prehliadača:
                </p>
                <p style="word-break:break-all; margin:0 0 16px;">
                    <a href="{{ $reset_url }}" style="color:#1868DB; text-decoration:underline;">{{ $reset_url }}</a>
                </p>

                <p style="margin:15px 0; color:#d9534f; font-weight:600; font-size:14px; line-height:1.5;">
                    Ak ste o zmenu hesla nežiadali, ignorujte tento e-mail.
                </p>

                <hr style="margin:30px 0; border:none; border-top:1px solid #eee;">

                <div style="font-size:13px; color:#555; margin-top:10px; text-align:left;">
                    <p style="margin:0 0 6px;">🔒 Tipy pre bezpečnosť:</p>
                    <ul style="margin:6px 0 0 20px; padding:0; text-align:left;">
                        <li style="margin:4px 0;">Odkaz nepreposielajte nikomu.</li>
                        <li style="margin:4px 0;">Po nastavení nového hesla ho s nikým nezdieľajte.</li>
                    </ul>
                </div>

                <p style="font-size:12px; color:#6b7280; margin-top:24px;">
                    Tento e-mail bol odoslaný automaticky, neodpovedajte naň.
                </p>
            </div>
        </td>
    </tr>
</table>
</body>
</html>
