{{-- resources/views/emails/internship_confirmed_to_approved.blade.php --}}

<p>Dobrý deň {{ $student_name }},</p>

<p>
    Vaša prax u spoločnosti <strong>{{ $company_name }}</strong>
    bola úspešne schválená garantom.
</p>

<p><strong>Detaily praxe:</strong></p>

<ul>
    <li><strong>Firma:</strong> {{ $company_name }}</li>
    <li><strong>Semester:</strong> {{ $academic_year }}</li>
    <li><strong>Obdobie:</strong> {{ $start_date }} – {{ $end_date }}</li>
</ul>

<p>
    V prípade otázok kontaktujte garanta.
</p>

<p>
    S pozdravom<br>
    <strong>Systém evidencie praxí</strong>
</p>
