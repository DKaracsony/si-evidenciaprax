<?php

return [
    'custom' => [
        'first_name' => [
            'required' => 'Meno je povinné.',
            'string' => 'Meno musí byť text.',
            'min' => 'Meno musí mať aspoň 2 znaky.',
            'max' => 'Meno môže mať najviac 50 znakov.',
        ],

        'last_name' => [
            'required' => 'Priezvisko je povinné.',
            'string' => 'Priezvisko musí byť text.',
            'min' => 'Priezvisko musí mať aspoň 2 znaky.',
            'max' => 'Priezvisko môže mať najviac 50 znakov.',
        ],

        'title_before' => [
            'string' => 'Titul pred menom musí byť text.',
            'max' => 'Titul pred menom môže mať najviac 30 znakov.',
        ],

        'email' => [
            'required' => 'E-mailová adresa je povinná.',
            'email' => 'Zadaj platnú e-mailovú adresu.',
            'max' => 'E-mailová adresa môže mať najviac 255 znakov.',
            'unique' => 'Táto e-mailová adresa je už zaregistrovaná.',
        ],

        'phone_number' => [
            'required' => 'Telefónne číslo je povinné.',
            'string' => 'Telefónne číslo musí byť text.',
            'min' => 'Telefónne číslo musí mať aspoň 8 znakov.',
            'max' => 'Telefónne číslo môže mať najviac 20 znakov.',
        ],

        'city' => [
            'required' => 'Mesto je povinné.',
            'string' => 'Mesto musí byť text.',
            'min' => 'Mesto musí mať aspoň 2 znaky.',
            'max' => 'Mesto môže mať najviac 100 znakov.',
        ],

        'street' => [
            'required' => 'Ulica je povinná.',
            'string' => 'Ulica musí byť text.',
            'min' => 'Ulica musí mať aspoň 2 znaky.',
            'max' => 'Ulica môže mať najviac 100 znakov.',
        ],

        'house_number' => [
            'required' => 'Číslo domu je povinné.',
            'integer' => 'Číslo domu musí byť celé číslo.',
            'min' => 'Číslo domu musí byť aspoň 1.',
            'digits_between' => 'Číslo domu musí mať 1 až 10 číslic.',
        ],

        'postal_code' => [
            'required' => 'PSČ je povinné.',
            'string' => 'PSČ musí byť text.',
            'min' => 'PSČ musí mať aspoň 3 znaky.',
            'max' => 'PSČ môže mať najviac 10 znakov.',
        ],

        'country' => [
            'required' => 'Krajina je povinná.',
            'integer' => 'Krajina musí byť zadaná ako číslo.',
            'exists' => 'Zvolená krajina neexistuje.',
        ],

        // ==== ŠTUDENT ====
        'student_email' => [
            'required' => 'Univerzitná e-mailová adresa je povinná.',
            'string' => 'Univerzitná e-mailová adresa musí byť text.',
            'max' => 'Univerzitná e-mailová adresa môže mať najviac 255 znakov.',
            'email' => 'Zadaj platnú univerzitnú e-mailovú adresu.',
            'regex' => 'Použi tvar meno.priezvisko alebo meno.stredne.priezvisko a doména musí byť student.ukf.sk.',
            'unique' => 'Táto univerzitná e-mailová adresa je už zaregistrovaná.',
        ],

        'faculty' => [
            'required' => 'Študijný odbor je povinný.',
            'integer' => 'Študijný odbor musí byť zadaný ako číslo.',
            'exists' => 'Zvolený študijný odbor neexistuje.',
        ],

        // ==== FIRMA ====


        // ==== HESLO / PASSWORD ====
        'password' => [
            'required' => 'Pole heslo je povinné.',
            'string' => 'Heslo musí byť text.',
            'min' => 'Heslo musí mať aspoň 8 znakov.',
            'confirmed' => 'Potvrdenie hesla sa nezhoduje.',
        ],

        'current_password' => [
            'required' => 'Pole aktuálne heslo je povinné.',
            'string' => 'Aktuálne heslo musí byť text.',
        ],

        'new_password' => [
            'required' => 'Pole nové heslo je povinné.',
            'string' => 'Nové heslo musí byť text.',
            'min' => 'Nové heslo musí mať aspoň 8 znakov.',
            'confirmed' => 'Potvrdenie nového hesla sa nezhoduje.',
        ],

        'token' => [
            'required' => 'Token je povinný.',
            'string' => 'Token musí byť text.',
        ],

        //INTERNSHIP VALIDATIONS
        'start_date' => [
            'required' => 'Dátum začiatku je povinný.',
            'date' => 'Dátum začiatku musí byť platný dátum.',
        ],

        'date_to' => [
            'required' => 'Dátum ukončenia je povinný.',
            'date' => 'Dátum ukončenia musí byť platný dátum.',
            'after_or_equal' => 'Dátum ukončenia musí byť neskorší alebo rovnaký ako dátum začiatku.',
        ],

        'description' => [
            'required' => 'Popis je povinný.',
            'string' => 'Popis musí byť text.',
        ],

        'is_draft' => [
            'required' => 'Pole „koncept“ je povinné.',
            'boolean' => 'Pole „koncept“ musí mať hodnotu true alebo false.',
        ],

        'company_id' => [
            'required' => 'Spoločnosť je povinná.',
            'integer' => 'Spoločnosť musí byť platné ID.',
            'exists' => 'Vybraná spoločnosť neexistuje.',
        ],

        'academic_year_id' => [
            'required' => 'Akademický rok je povinný.',
            'integer' => 'Akademický rok musí byť platné ID.',
            'exists' => 'Vybraný akademický rok neexistuje.',
        ],

        'student_profile_id' => [
            'required' => 'Študentský profil je povinný.',
            'integer' => 'Študentský profil musí byť platné ID.',
            'exists' => 'Vybraný študentský profil neexistuje.',
        ],

        'faculty_ids' => [
            'required' => 'Pole fakulty je povinné.',
            'array' => 'Pole fakulty musí byť pole.',
        ],

        'faculty_ids.*' => [
            'integer' => 'ID fakulty musí byť celé číslo.',
            'exists' => 'Jedno z vybraných ID fakulty neexistuje.',
        ],

        'internship_id' => [
            'required' => 'Identifikátor praxe je povinný.',
            'integer' => 'Identifikátor praxe musí byť celé číslo.',
            'exists' => 'Vybraná prax neexistuje.',
        ],
        'document' => [
            'required' => 'Dokument je povinný.',
            'file' => 'Dokument musí byť súbor.',
            'mimes' => 'Dokument musí byť vo formáte PDF.',
            'max' => 'Dokument nesmie presiahnuť veľkosť 10 MB.',
        ],

        'document.*' => [
            'prohibited' => 'Viacnásobné nahrávanie dokumentov nie je povolené.',
        ],
    ],


];
