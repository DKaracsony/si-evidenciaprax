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
                    Aktivujte svoj firemný účet
                </h1>

                <p style="margin: 15px 0; color: #333; font-size: 16px; line-height: 1.5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                    Dobrý deň,
                    @if(isset($user))
                        <strong>{{ $user->first_name }} z firmy{{ $company->name }}</strong>
                    @else
                        <strong>vážený používateľ</strong>
                    @endif
                </p>

                <p style="margin: 15px 0; color: #333; font-size: 16px; line-height: 1.5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                    ďakujeme za registráciu vašej spoločnosti v systéme Evidencia praxí.<br>
                    Pred prihlásením aktivujte svoj účet kliknutím na odkaz nižšie:
                </p>


                <p style="margin-top: 25px;">
                    <a href="{{ $activationLink }}" class="button">
                        Aktivovať účet
                    </a>
                </p>

                <p style="margin: 15px 0; color: #d9534f; font-weight: 600; font-size: 16px; line-height: 1.5; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                    Tento odkaz je platný do: <strong>{{ $tokenExpiration }}</strong>
                </p>

                <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">

                <div style="font-size: 13px; color: #555; margin-top: 20px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
                    Aktivujte účet len ak ste vyplnili registračný formulár. Ak nie, ignorujte túto správu
                </div>
            </div>
        </td>
    </tr>
</table>
</body>
</html>




