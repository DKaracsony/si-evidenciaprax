<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5;">
<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="width: 100%;">
    <tr>
        <td align="center">
            <div style="max-width: 600px; margin: 0 auto; padding: 30px; background-color: white; border: 1px solid #ddd; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); text-align: center; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">

                <h1 style="color: #1868DB; font-size: 27px; margin-bottom: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                    Použite dočasné heslo na prihlásenie
                </h1>

                <p style="margin: 15px 0; color: #333; font-size: 16px; line-height: 1.5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                    Dobrý deň,
                    @if(isset($user))
                        <strong>{{ $user->first_name }}</strong>
                    @else
                        <strong>vážený používateľ</strong>
                    @endif
                </p>

                <p style="margin: 15px 0; color: #333; font-size: 16px; line-height: 1.5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                    váš firemný účet bol úspešne aktivovaný.<br>
                    Na prihlásenie použite nasledovné dočasné heslo:
                </p>

                <div style="display: inline-block; background-color: #f8f9fa; padding: 15px 20px; border: 2px solid #1868DB; border-radius: 8px; font-size: 20px; font-weight: bold; color: #1868DB; letter-spacing: 2px; margin: 15px 0; font-family: 'Courier New', Courier, monospace;">
                    @if(isset($temporaryPassword))
                        {{ $temporaryPassword }}
                    @else
                        [dočasné heslo]
                    @endif
                </div>

                <p style="margin-top: 25px;">
                    <a href="{{ $loginUrl ?? '' }}" style="display: inline-block; padding: 12px 24px; background-color: #1868DB; border: 4px solid #a9c9ff; color: white; text-decoration: none; box-shadow: 0 8px 18px rgba(0, 0, 0, 0.30); font-weight: 700; border-radius: 12px; margin: 10px 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; font-size: 16px;">
                        Prihlásiť sa
                    </a>
                </p>

                <p style="margin: 15px 0; color: #d9534f; font-weight: 600; font-size: 16px; line-height: 1.5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                    Z bezpečnostných dôvodov odporúčame heslo ihneď po prihlásení zmeniť.
                </p>

                <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">

                <div style="font-size: 13px; color: #555; margin-top: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                    🔒 Nezdieľajte svoje dočasné heslo. Po prihlásení si ho zmeňte.
                </div>
            </div>
        </td>
    </tr>
</table>
</body>
</html>
