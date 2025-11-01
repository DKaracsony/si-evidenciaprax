@component('mail::message')
    # Aktivujte svoj firemný účet

    Dobrý deň, {{ $activation->company->ownerProfiles->user->first_name }} z firmy **{{ $activation->company->name }}**,

    ďakujeme za registráciu vašej spoločnosti v systéme Evidencia praxí.
    Pred prihlásením aktivujte svoj účet kliknutím na odkaz nižšie:

    @component('mail::button', ['url' => $activationLink])
        Aktivovať účet
    @endcomponent

    Tento odkaz je platný do: **{{ $tokenExpiration }}**

    ---

    Aktivujte účet len ak ste vyplnili registračný formulár.
    Ak nie, ignorujte túto správu.

@endcomponent
