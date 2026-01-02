{{-- resources/views/emails/internship_approved_to_not_defended.blade.php --}}

<p>Dobrý deň {{ $student_name }},</p>

<p>
    Vaša prax u spoločnosti <strong>{{ $company_name }}</strong>
    nebola obhájená.
</p>

@if(!empty($note))
    <p><strong>Dôvod:</strong></p>
    <p>{{ $note }}</p>
@endif

<p>
    V prípade nejasností kontaktujte garanta.
</p>

<p>
    S pozdravom<br>
    <strong>Systém evidencie praxí</strong>
</p>
