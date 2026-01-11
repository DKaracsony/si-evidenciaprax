@php use App\Models\Role; @endphp
<p>Dobrý deň {{ $to_role == Role::STUDENT ? $student_name : $company_profile_name }},</p>

@if($to_role == Role::STUDENT)
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
@else
    <p>
        Prax študenta <strong>{{ $student_name }}</strong>
        vo vašej spoločnosti <strong>{{ $company_name }}</strong>
        bola úspešne schválená garantom.
    </p>
@endif

<p>
    S pozdravom<br>
    <strong>Systém evidencie praxí</strong>
</p>
