@php
    $studentProfile = $studentProfile ?? $internship->studentProfile;
    $studentUser    = $studentUser ?? $studentProfile?->user;
    $company        = $company ?? $internship->company;

    $studentFullName = trim(
        ($studentUser->title_before ? $studentUser->title_before.' ' : '') .
        $studentUser->first_name.' '.$studentUser->last_name .
        ($studentUser->title_after ? ', '.$studentUser->title_after : '')
    );

    $studentEmail = $studentProfile->student_email
        ?? $studentProfile->personal_email
        ?? $studentUser?->email
        ?? '';

    $studentPhone = $studentUser->phone_number
        ?? $studentUser->phone_num
        ?? '';

    $studyProgram = $studentProfile?->faculty?->name ?? 'aplikovaná informatika';

    $studentAddress = implode(', ', array_filter([
        ($studentProfile?->address?->street && $studentProfile?->address?->house_number)
            ? $studentProfile->address->street.' '.$studentProfile->address->house_number
            : null,
        ($studentProfile?->address?->postal_code || $studentProfile?->address?->city)
            ? trim(($studentProfile->address->postal_code ?? '').' '.($studentProfile->address->city ?? ''))
            : null,
        $studentProfile?->address?->country?->name,
    ]));

    $companyAddress = implode(', ', array_filter([
        ($company?->address?->street && $company?->address?->house_number)
            ? $company->address->street.' '.$company->address->house_number
            : null,
        ($company?->address?->postal_code || $company?->address?->city)
            ? trim(($company->address->postal_code ?? '').' '.($company->address->city ?? ''))
            : null,
        $company?->address?->country?->name,
    ]));

    $companyRepFullName = trim(
        ($companyOwnerUser->title_before ? $companyOwnerUser->title_before.' ' : '') .
        $companyOwnerUser->first_name.' '.$companyOwnerUser->last_name .
        ($companyOwnerUser->title_after ? ', '.$companyOwnerUser->title_after : '')
    );

    $companyRepRole = $companyOwnerProfile->role_at_company ?? '';

    $dateFrom = $internship->start_date
        ? \Carbon\Carbon::parse($internship->start_date)->format('d. m. Y')
        : '';

    $dateTo = $internship->date_to
        ? \Carbon\Carbon::parse($internship->date_to)->format('d. m. Y')
        : '';

    $today = now()->format('d. m. Y');

    $companyCity = $company?->address?->city ?? '';
    $companyCityLoc = ($companyCity === 'Nitra') ? 'Nitre' : $companyCity;
@endphp

    <!DOCTYPE html>
<html lang="sk">
<head>
    <title>Dohoda o odbornej praxi študenta</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta charset="utf-8">
    <style>
        @page { margin: 25mm 25mm; }

        body {
            margin: 0;
            font-family: "DejaVu Serif", serif;
            font-size: 11pt;
            line-height: 1.15;
            color: #000;
        }

        p, td, div {
            overflow-wrap: break-word;
            word-break: break-word;
        }

        p { margin: 0 0 4pt 0; }

        .center { text-align: center; }
        .bold { font-weight: bold; }

        .indent  { margin-left: 15mm; }

        .title {
            font-weight: bold;
            text-transform: uppercase;
        }

        .section {
            font-weight: bold;
            text-align: center;
            margin: 12pt 0 8pt;
        }

        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding-bottom: 3pt; }

        small { font-size: 10pt; }

        .sign-line{
            border-bottom: 1px dotted #000;
            width: 80%;
            margin: 18pt auto 6pt auto;
        }

        .wrap-15 { margin-left: 15mm; }
        .wrap-30 { margin-left: 30mm; }
        .wrap-45 { margin-left: 45mm; }

        .numtbl, .subtbl{
            width: 100%;
            border-collapse: collapse;
            margin-left: 0;
            margin-top: 2pt;
        }

        .numtbl td, .subtbl td{
            padding: 0 0 4pt 0;
            vertical-align: top;
        }

        .numtbl .n{
            width: 14mm;
            white-space: nowrap;
        }

        .subtbl .n{
            width: 10mm;
            white-space: nowrap;
        }

        .dash{
            margin: 0 0 4pt 0;
        }
    </style>
</head>

<body>

<p class="center" style="font-style: italic; margin-bottom: 10pt;">
    Platnosť tlačiva od 1.10.2022 (aplikovaná informatika)
</p>

<p class="center bold title">DOHODA O ODBORNEJ PRAXI ŠTUDENTA</p>

<p class="center" style="margin-bottom: 10pt;">
    uzatvorená v zmysle § 51 Občianskeho zákonníka a Zákona č. 131/2002 Z. z.<br>
    o vysokých školách
</p>

<p class="bold">Univerzita Konštantína Filozofa v Nitre</p>
<p class="indent">Fakulta prírodných vied a informatiky</p>
<p class="indent">Trieda A. Hlinku 1, 949 01 Nitra</p>
<p class="indent">v zastúpení prof. RNDr. František Petrovič, PhD. – dekan fakulty</p>
<p class="indent">e-mail: fpetrovic@ukf.sk, tel. 037/6408 555</p>

<p class="bold" style="margin-top:12pt;">
    Poskytovateľ odbornej praxe (organizácia, resp. inštitúcia)
</p>

<p class="indent">Plný názov a adresa</p>
<p class="indent">{{ $company->name }}, {{ $companyAddress }}</p>

<p class="indent" style="margin-top:8pt;">v zastúpení</p>
<p class="indent">{{ $companyRepFullName }}@if($companyRepRole), {{ $companyRepRole }}@endif</p>

<p class="bold" style="margin-top:12pt;">Študent:</p>

<div class="wrap-15">
    <table style="margin-top:4pt;">
        <tr>
            <td style="width:40%;">Meno a priezvisko:</td>
            <td>{{ $studentFullName }}</td>
        </tr>
        <tr>
            <td>Adresa trvalého bydliska:</td>
            <td>{{ $studentAddress }}</td>
        </tr>
        <tr>
            <td>Kontakt študenta FPVaI UKF v Nitre:</td>
            <td>{{ $studentEmail }}@if($studentPhone), tel. {{ $studentPhone }}@endif</td>
        </tr>
        <tr>
            <td>Študijný program:</td>
            <td>{{ $studyProgram }}</td>
        </tr>
    </table>
</div>

<p style="margin-top:10pt;">uzatvárajú túto dohodu o odbornej praxi študenta.</p>

<p class="section">I. Predmet dohody</p>

<p>
    Predmetom tejto dohody je vykonanie odbornej praxe študenta v rozsahu 150 hodín,
    v termíne od {{ $dateFrom }} do {{ $dateTo }} bezodplatne.
</p>

<p class="section">II. Práva a povinnosti účastníkov dohody</p>

<p class="bold" style="margin-top:10pt;">
    1. Fakulta prírodných vied a informatiky Univerzity Konštantína Filozofa v Nitre:
</p>

<div class="wrap-15">
    <table class="numtbl">
        <tr>
            <td class="n">1.1</td>
            <td class="t">
                Poverí svojho zamestnanca: Mgr. Martin Vozár, PhD. (mvozar@ukf.sk) za 1. stupeň,
                PaedDr. Peter Švec, Ph.D. (psvec@ukf.sk) za 2. stupeň
            </td>
        </tr>
        <tr>
            <td class="n"></td>
            <td class="t">(ďalej garant odbornej praxe) garanciou odbornej praxe.</td>
        </tr>
        <tr>
            <td class="n">1.2</td>
            <td class="t">Prostredníctvom garanta odbornej praxe:</td>
        </tr>
    </table>
</div>

<div class="wrap-30">
    <table class="subtbl">
        <tr>
            <td class="n">a)</td>
            <td class="t">poskytne študentovi:</td>
        </tr>
    </table>
</div>

<div class="wrap-45">
    <p class="dash">
        - informácie o organizácii praxe, o podmienkach dojednania dohody o odbornej praxi, o obsahovom zameraní odbornej praxe
        a o požiadavkách na obsahovú náplň správy z odbornej praxe,
    </p>
    <p class="dash">- návrh dohody o odbornej praxi študenta,</p>
</div>

<div class="wrap-30">
    <table class="subtbl">
        <tr>
            <td class="n">b)</td>
            <td class="t">
                rozhodne o udelení hodnotenia „ABS" (absolvoval) študentovi na základe dokladu „Výkaz o vykonanej odbornej praxi",
                vydaného poskytovateľom odbornej praxe a na základe študentom vypracovanej správy o odbornej praxi,
                ktorej súčasťou je verejná obhajoba výsledkov odbornej praxe,
            </td>
        </tr>
        <tr>
            <td class="n">c)</td>
            <td class="t">spravuje vyplnenú a účastníkmi podpísanú dohodu o odbornej praxi.</td>
        </tr>
    </table>
</div>

<p class="bold" style="margin-top:10pt;">2. Poskytovateľ odbornej praxe:</p>

<div class="wrap-15">
    <table class="numtbl">
        <tr>
            <td class="n">2.1</td>
            <td class="t">
                poverí svojho zamestnanca (tútor – zodpovedný za odbornú prax v organizácii)
                {{ $companyRepFullName }}, ktorý bude dohliadať na dodržiavanie dohody o odbornej praxi,
                plnenie obsahovej náplne odbornej praxe a bude nápomocný pri získavaní potrebných údajov pre vypracovanie správy z odbornej praxe,
            </td>
        </tr>
        <tr>
            <td class="n">2.2</td>
            <td class="t">
                na začiatku praxe vykoná poučenie o bezpečnosti a ochrane zdravia pri práci v zmysle platných predpisov,
            </td>
        </tr>
        <tr>
            <td class="n">2.3</td>
            <td class="t">
                vzniknuté organizačné problémy súvisiace s plnením dohody rieši spolu s garantom odbornej praxe,
            </td>
        </tr>
        <tr>
            <td class="n">2.4</td>
            <td class="t">
                po ukončení odbornej praxe vydá študentovi „Výkaz o vykonanej odbornej praxi",
                ktorý obsahuje popis vykonávaných činností a stručné hodnotenie študenta a je jedným z predpokladov úspešného ukončenia predmetu Odborná prax,
            </td>
        </tr>
        <tr>
            <td class="n">2.5</td>
            <td class="t">
                umožní garantovi odbornej praxe a garantovi študijného predmetu kontrolu študentom plnených úloh.
            </td>
        </tr>
    </table>
</div>

<p class="bold" style="margin-top:10pt;">3. Študent FPVaI UKF v Nitre:</p>

<div class="wrap-15">
    <table class="numtbl">
        <tr>
            <td class="n">3.1</td>
            <td class="t">osobne zabezpečí podpísanie tejto dohody o odbornej praxi študenta,</td>
        </tr>
        <tr>
            <td class="n">3.2</td>
            <td class="t">zodpovedne vykonáva činnosti pridelené tútorom odbornej praxe,</td>
        </tr>
        <tr>
            <td class="n">3.3</td>
            <td class="t">
                zabezpečí doručenie dokladu „Výkaz o vykonanej odbornej praxi" najneskôr v termínoch predpísaných garantom pre daný semester,
            </td>
        </tr>
        <tr>
            <td class="n">3.4</td>
            <td class="t">
                okamžite, bez zbytočného odkladu informuje garanta odbornej praxe o problémoch, ktoré bránia plneniu odbornej praxe.
            </td>
        </tr>
    </table>
</div>

<p class="section">III. Všeobecné a záverečné ustanovenia</p>

<div class="wrap-15">
    <table class="numtbl">
        <tr>
            <td class="n">1.</td>
            <td class="t">
                Dohoda sa uzatvára na dobu určitú. Dohoda nadobúda platnosť a účinnosť dňom podpísania obidvomi zmluvnými stranami.
                Obsah dohody sa môže meniť písomne len po súhlase jej zmluvných strán.
            </td>
        </tr>
        <tr>
            <td class="n">2.</td>
            <td class="t">
                Dohoda sa uzatvára v 3 vyhotoveniach, každá zmluvná strana obdrží jedno vyhotovenie dohody.
            </td>
        </tr>
    </table>
</div>

<table style="margin-top:25pt;">
    <tr>
        <td style="width:50%;">V Nitre, dňa {{ $today }}</td>
        <td style="width:50%;">V {{ $companyCityLoc }}, dňa {{ $today }}</td>
    </tr>
</table>

<table style="margin-top:35pt;">
    <tr>
        <td style="width:50%; text-align:center;">
            <div class="sign-line"></div>
            <small>prof. RNDr. František Petrovič, PhD.</small><br>
            <small>dekan FPVaI UKF v Nitre</small>
        </td>
        <td style="width:50%; text-align:center;">
            <div class="sign-line"></div>
            <small>{{ $companyRepFullName }}</small><br>
            <small>štatutárny zástupca pracoviska odb. praxe</small>
        </td>
    </tr>
</table>

<table style="margin-top:35pt;">
    <tr>
        <td style="width:50%;"></td>
        <td style="width:50%; text-align:center;">
            <div class="sign-line"></div>
            <small>{{ $studentFullName }}</small>
        </td>
    </tr>
</table>

</body>
</html>
