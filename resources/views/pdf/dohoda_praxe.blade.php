@php
    /**
     * @var \App\Models\Internship          $internship
     * @var \App\Models\StudentProfile      $studentProfile
     * @var \App\Models\User                $studentUser
     * @var \App\Models\Company             $company
     * @var \App\Models\User                $companyOwnerUser
     * @var \App\Models\CompanyOwnerProfile $companyOwnerProfile
     * @var \App\Models\AcademicYear        $academicYear
     */

    $studentProfile = $studentProfile ?? $internship->studentProfile;
    $studentUser    = $studentUser ?? $studentProfile?->user;
    $company        = $company ?? $internship->company;

    // Meno študenta
    $studentFullName = $studentUser
        ? trim(
            ($studentUser->title_before ? $studentUser->title_before . ' ' : '') .
            $studentUser->first_name . ' ' .
            $studentUser->last_name .
            ($studentUser->title_after ? ', ' . $studentUser->title_after : '')
        )
        : '';

    $studentEmail = $studentProfile->student_email
        ?? $studentProfile->personal_email
        ?? $studentUser?->email
        ?? '';

    $studentPhone = $studentUser->phone_number
        ?? $studentUser->phone_num
        ?? null;

    $studyProgram = $studentProfile?->faculty?->name ?? '';

    // Adresa študenta
    $studentAddress = implode(', ', array_filter([
        ($studentProfile?->address?->street && $studentProfile?->address?->house_number)
            ? $studentProfile->address->street . ' ' . $studentProfile->address->house_number
            : null,
        ($studentProfile?->address?->postal_code || $studentProfile?->address?->city)
            ? trim(($studentProfile->address->postal_code ?? '') . ' ' . ($studentProfile->address->city ?? ''))
            : null,
        $studentProfile?->address?->country?->name,
    ]));

    // Adresa firmy
    $companyAddress = implode(', ', array_filter([
        ($company?->address?->street && $company?->address?->house_number)
            ? $company->address->street . ' ' . $company->address->house_number
            : null,
        ($company?->address?->postal_code || $company?->address?->city)
            ? trim(($company->address->postal_code ?? '') . ' ' . ($company->address->city ?? ''))
            : null,
        $company?->address?->country?->name,
    ]));

    // Zástupca firmy
    $companyRepFullName = trim(
        ($companyOwnerUser->title_before ? $companyOwnerUser->title_before . ' ' : '') .
        $companyOwnerUser->first_name . ' ' .
        $companyOwnerUser->last_name .
        ($companyOwnerUser->title_after ? ', ' . $companyOwnerUser->title_after : '')
    );

    $companyRepRole = $companyOwnerProfile->role_at_company ?? '';

    // Dátumy praxe
    $dateFrom = $internship->start_date
        ? \Carbon\Carbon::parse($internship->start_date)->format('d. m. Y')
        : '';

    $dateTo = $internship->date_to
        ? \Carbon\Carbon::parse($internship->date_to)->format('d. m. Y')
        : '';

    $today = now()->format('d. m. Y');
@endphp

    <!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <title>Dohoda o odbornej praxi študenta</title>
    <style>
        @page {
            margin: 20mm 20mm;
        }

        body {
            font-family: "DejaVu Serif", serif;
            font-size: 12pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            color: #000;
            background: #ffffff;
        }

        .page {
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 10px 0 6px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
        }

        .subtitle {
            text-align: center;
            font-size: 10.5pt;
            margin: 6px 0 16px 0;
            font-style: italic;
            color: #333;
        }

        .header-section {
            margin-bottom: 14px;
            padding: 8px 10px;
            background: #f9f9f9;
            border-left: 3px solid #1a1a1a;
        }

        .header-section h2 {
            font-size: 12pt;
            font-weight: bold;
            margin: 0 0 6px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-section p {
            margin: 2px 0;
            line-height: 1.4;
        }

        .section-title {
            font-weight: bold;
            font-size: 12.5pt;
            margin: 18px 0 8px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #333;
            padding-bottom: 4px;
        }

        p {
            margin: 6px 0;
            text-align: justify;
            text-indent: 0;
        }

        .info-label {
            font-weight: bold;
        }

        ul {
            margin: 4px 0 6px 25px;
            padding-left: 15px;
            list-style-type: disc;
        }

        ul li {
            margin: 3px 0;
            text-align: justify;
            line-height: 1.4;
        }

        .subsection {
            margin: 6px 0 8px 15px;
        }

        .subsection p {
            margin: 4px 0;
        }

        .signature-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .signature-row {
            display: table;
            width: 100%;
            margin-top: 25px;
        }

        .signature-column {
            display: table-cell;
            vertical-align: top;
            width: 50%;
            padding: 0 10px;
        }

        .signature-block {
            text-align: center;
            margin-bottom: 35px;
        }

        .signature-line {
            border-top: 1.2px solid #000;
            margin: 30px auto 6px auto;
            width: 100%;
        }

        .signature-text {
            font-size: 10.5pt;
            margin: 2px 0;
            line-height: 1.3;
        }

        .date-location {
            margin: 15px 0 8px 0;
            font-style: italic;
        }

        .numbered-clause {
            margin: 8px 0;
        }

        .legal-text {
            line-height: 1.5;
        }
    </style>

</head>
<body>
<div class="page" style="padding: 15mm 20mm;">

    {{-- Hlavička dokumentu --}}
    <h1>Dohoda o odbornej praxi študenta</h1>
    <p class="subtitle">
        uzatvorená podľa § 51 Občianskeho zákonníka a Zákona č. 131/2002 Z. z.
        o vysokých školách medzi vysokou školou, poskytovateľom praxe a študentom
    </p>

    {{-- Univerzita --}}
    <div class="header-section">
        <h2>Univerzita Konštantína Filozofa v Nitre</h2>
        <p>Fakulta prírodných vied a informatiky</p>
        <p>Trieda A. Hlinku 1, 949 01 Nitra</p>
        <p>v zastúpení dekana fakulty Dr. h. c. prof. RNDr. František Petrovič, PhD., MBA</p>
        <p>kontaktné údaje fakulty dostupné na oficiálnej webovej stránke</p>
    </div>

    {{-- Poskytovateľ odbornej praxe --}}
    <div class="header-section">
        <h2>Poskytovateľ odbornej praxe (organizácia, resp. inštitúcia)</h2>
        <p><strong>Plný názov a adresa:</strong></p>
        <p>{{ $company->name }}</p>
        <p>{{ $companyAddress }}</p>
        <p style="margin-top: 10px;">
            <strong>v zastúpení:</strong>
            {{ $companyRepFullName }}@if($companyRepRole), {{ $companyRepRole }}@endif
        </p>
    </div>

    {{-- Študent --}}
    <div class="header-section">
        <h2>Študent:</h2>
        <p><span class="info-label">Meno a priezvisko:</span> {{ $studentFullName }}</p>
        <p><span class="info-label">Adresa trvalého bydliska:</span> {{ $studentAddress }}</p>
        <p>
            <span class="info-label">Kontakt študenta:</span>
            {{ $studentEmail }}
            @if(!empty($studentUser->phone_num)), tel.: {{ $studentUser->phone_num }}@endif
        </p>
        <p>
            <span class="info-label">Študijný program:</span>
            {{ $studyProgram }}
        </p>
    </div>

    <p style="margin-top: 20px;">
        Fakulta, poskytovateľ odbornej praxe a študent uzatvárajú túto dohodu o odbornej praxi študenta.
    </p>

    {{-- I. Predmet dohody --}}
    <div class="section-title">I. Predmet dohody</div>
    <p>
        Predmetom tejto dohody je vykonanie odbornej praxe študenta v študijnom programe fakulty
        v celkovom rozsahu najmenej 150 hodín v období od {{ $dateFrom }} do {{ $dateTo }}.
        Prax sa vykonáva u poskytovateľa odbornej praxe na pracovisku dohodnutom so študentom
        a je vykonávaná bez nároku na mzdu, ak sa zmluvné strany nedohodnú inak.
    </p>

    {{-- II. Práva a povinnosti --}}
    <div class="section-title">II. Práva a povinnosti účastníkov dohody</div>

    <p class="numbered-clause">
        <strong>1. Fakulta:</strong>
    </p>

    <div class="subsection">
        <p>
            <strong>1.1</strong> Vymenuje garanta odbornej praxe, ktorý zabezpečuje odborné vedenie,
            koordináciu a administratívne náležitosti súvisiace s odbornou praxou študenta.
        </p>

        <p>
            <strong>1.2</strong> Prostredníctvom garanta odbornej praxe fakulta:
        </p>

        <ul>
            <li>informuje študenta o podmienkach, organizácii a cieľoch odbornej praxe,</li>
            <li>poskytne študentovi podklady a formuláre potrebné na uzatvorenie a evidenciu dohody,</li>
            <li>stanoví požiadavky na obsah správy z odbornej praxe a spôsob jej odovzdania.</li>
        </ul>

        <p>
            <strong>1.3</strong> Garant odbornej praxe hodnotí splnenie podmienok praxe
            na základe potvrdenia od poskytovateľa praxe a predloženej správy študenta.
        </p>
    </div>

    <p class="numbered-clause">
        <strong>2. Poskytovateľ odbornej praxe:</strong>
    </p>

    <div class="subsection">
        <p>
            <strong>2.1</strong> Určí zodpovednú osobu (tútora praxe), ktorá koordinuje činnosti študenta
            na pracovisku, zadáva mu úlohy a dohliada na ich plnenie.
        </p>

        <p>
            <strong>2.2</strong> Na začiatku praxe zabezpečí proškolenie študenta
            z hľadiska bezpečnosti a ochrany zdravia pri práci a oboznámenie s vnútornými predpismi.
        </p>

        <p>
            <strong>2.3</strong> V prípade vzniku problémov pri plnení praxe spolupracuje s garantom odbornej praxe
            na ich riešení.
        </p>

        <p>
            <strong>2.4</strong> Po ukončení praxe vystaví študentovi písomné potvrdenie o absolvovaní odbornej praxe,
            ktoré obsahuje stručný popis vykonávaných činností a hodnotenie študenta.
        </p>

        <p>
            <strong>2.5</strong> Umožní garantovi odbornej praxe primeranú kontrolu priebehu odbornej praxe študenta.
        </p>
    </div>

    <p class="numbered-clause">
        <strong>3. Študent:</strong>
    </p>

    <div class="subsection">
        <p>
            <strong>3.1</strong> Zabezpečí, aby bola táto dohoda riadne podpísaná všetkými zmluvnými stranami
            pred začiatkom vykonávania odbornej praxe.
        </p>

        <p>
            <strong>3.2</strong> Počas praxe zodpovedne plní pridelené úlohy, dodržiava pracovnú disciplínu,
            interné predpisy poskytovateľa praxe a zásady bezpečnosti a ochrany zdravia pri práci.
        </p>

        <p>
            <strong>3.3</strong> Po ukončení praxe odovzdá fakulte požadované podklady (potvrdenie o absolvovaní praxe,
            správu z odbornej praxe) v termíne určenom fakultou.
        </p>

        <p>
            <strong>3.4</strong> Bez zbytočného odkladu informuje garanta odbornej praxe o skutočnostiach,
            ktoré bránia riadnemu plneniu odbornej praxe.
        </p>
    </div>

    {{-- III. Záverečné ustanovenia --}}
    <div class="section-title">III. Záverečné ustanovenia</div>

    <p class="numbered-clause legal-text">
        <strong>1.</strong>
        Dohoda sa uzatvára na dobu určitú, a to na obdobie výkonu odbornej praxe
        podľa článku I. Dohoda nadobúda platnosť a účinnosť dňom jej podpísania všetkými zmluvnými stranami.
        Zmeny a doplnenia tejto dohody sú možné len písomnou formou po dohode všetkých zúčastnených strán.
    </p>

    <p class="numbered-clause legal-text">
        <strong>2.</strong>
        Dohoda sa vyhotovuje v troch rovnopisoch, z ktorých jeden obdrží fakulta,
        jeden poskytovateľ odbornej praxe a jeden študent.
    </p>

    {{-- Podpisy --}}
    <div class="signature-section">
        <div class="date-location">
            <p>V Nitre, dňa {{ $today }}</p>
        </div>

        <div class="signature-row">
            {{-- Ľavá strana: dekan --}}
            <div class="signature-column">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <p class="signature-text">dekan Fakulty prírodných vied a informatiky UKF v Nitre</p>
                </div>
            </div>

            {{-- Pravá strana: poskytovateľ hore, študent dole --}}
            <div class="signature-column">
                <div class="signature-block">
                    <div class="signature-line"></div>
                    <p class="signature-text">{{ $companyRepFullName }}</p>
                    <p class="signature-text">poskytovateľ odbornej praxe</p>
                </div>

                {{-- Pod firmou: študent --}}
                <div class="signature-block" style="margin-top: 20px;">
                    <div class="signature-line"></div>
                    <p class="signature-text">{{ $studentFullName }}</p>
                    <p class="signature-text">študent</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
