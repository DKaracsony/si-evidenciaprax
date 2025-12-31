{{-- resources/views/emails/internship_approved_to_defended.blade.php --}}

<p>Dobrý deň {{ $student_name }},</p>

<p>
    Vaša prax u spoločnosti <strong>{{ $company_name }}</strong>
    bola úspešne obhájená.
</p>

<p>
    <strong>Gratulujeme k úspešnému ukončeniu praxe.</strong>
</p>

<p><strong>Detaily praxe:</strong></p>

<ul>
    <li><strong>Firma:</strong> {{ $company_name }}</li>
    <li><strong>Semester:</strong> {{ $academic_year }}</li>
</ul>

<p>
    S pozdravom<br>
    <strong>Systém evidencie praxí</strong>
</p>
