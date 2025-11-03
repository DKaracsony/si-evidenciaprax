{{--TODO: Atus update this mail template, this is just a tiny first example, see for example student password mail template--}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .button { display: inline-block; padding: 10px 20px; background-color: #3490dc; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Firma – Aktivačný e-mail</h1>

    <p>Dobrý deň, {{ $user->first_name }} z firmy {{ $company->name }},</p>

    <p>ďakujeme za registráciu vašej spoločnosti v systéme Evidencia praxí. Pred prihlásením aktivujte svoj účet kliknutím na odkaz nižšie:</p>

    <p><a href="{{ $activationLink }}" class="button">Aktivovať účet</a></p>

    <p>Tento odkaz je platný do: <strong>{{ $tokenExpiration }}</strong></p>

    <hr>

    <p>Aktivujte účet len ak ste vyplnili registračný formulár. Ak nie, ignorujte túto správu.</p>
</div>
</body>
</html>
