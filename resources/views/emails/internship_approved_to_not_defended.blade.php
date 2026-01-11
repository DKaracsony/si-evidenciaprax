@php use App\Models\Role; @endphp
<p>Dobrý deň {{ $to_role == Role::STUDENT ? $student_name : $company_profile_name }},</p>

@if($to_role == Role::STUDENT)
    <p>
        Vaša prax u spoločnosti <strong>{{ $company_name }}</strong>
        nebola obhájená.
    </p>

    @if(!empty($note))
        <p><strong>Dôvod:</strong></p>
        <p><i>{{ $note }}</i></p>
    @endif

    <p>
        V prípade nejasností kontaktujte garanta.
    </p>
@else
    <p>
        Prax študenta <strong>{{ $student_name }}</strong>
        vo vašej spoločnosti <strong>{{ $company_name }}</strong>
        nebola obhájená.
    </p>
@endif

<p>
    S pozdravom<br>
    <strong>Systém evidencie praxí</strong>
</p>
