<?php

return [
    'custom' => [
        'first_name' => [
            'required' => 'Meno je povinné.',
            'string'   => 'Meno musí byť text.',
            'min'      => 'Meno musí mať aspoň 2 znaky.',
            'max'      => 'Meno môže mať najviac 50 znakov.',
        ],

        'last_name' => [
            'required' => 'Priezvisko je povinné.',
            'string'   => 'Priezvisko musí byť text.',
            'min'      => 'Priezvisko musí mať aspoň 2 znaky.',
            'max'      => 'Priezvisko môže mať najviac 50 znakov.',
        ],

        'title_before' => [
            'string' => 'Titul pred menom musí byť text.',
            'max'    => 'Titul pred menom môže mať najviac 30 znakov.',
        ],

        'email' => [
            'required' => 'E-mailová adresa je povinná.',
            'email'    => 'Zadaj platnú e-mailovú adresu.',
            'max'      => 'E-mailová adresa môže mať najviac 255 znakov.',
            'unique'   => 'Táto e-mailová adresa je už zaregistrovaná.',
        ],

        'phone_number' => [
            'required' => 'Telefónne číslo je povinné.',
            'string'   => 'Telefónne číslo musí byť text.',
            'min'      => 'Telefónne číslo musí mať aspoň 8 znakov.',
            'max'      => 'Telefónne číslo môže mať najviac 20 znakov.',
        ],

        'city' => [
            'required' => 'Mesto je povinné.',
            'string'   => 'Mesto musí byť text.',
            'min'      => 'Mesto musí mať aspoň 2 znaky.',
            'max'      => 'Mesto môže mať najviac 100 znakov.',
        ],

        'street' => [
            'required' => 'Ulica je povinná.',
            'string'   => 'Ulica musí byť text.',
            'min'      => 'Ulica musí mať aspoň 2 znaky.',
            'max'      => 'Ulica môže mať najviac 100 znakov.',
        ],

        'house_number' => [
            'required'       => 'Číslo domu je povinné.',
            'integer'        => 'Číslo domu musí byť celé číslo.',
            'min'            => 'Číslo domu musí byť aspoň 1.',
            'digits_between' => 'Číslo domu musí mať 1 až 10 číslic.',
        ],

        'postal_code' => [
            'required' => 'PSČ je povinné.',
            'string'   => 'PSČ musí byť text.',
            'min'      => 'PSČ musí mať aspoň 3 znaky.',
            'max'      => 'PSČ môže mať najviac 10 znakov.',
        ],

        'country' => [
            'required' => 'Krajina je povinná.',
            'integer'  => 'Krajina musí byť zadaná ako číslo.',
            'exists'   => 'Zvolená krajina neexistuje.',
        ],

        // ==== ŠTUDENT ====
        'student_email' => [
            'required' => 'Univerzitná e-mailová adresa je povinná.',
            'string'   => 'Univerzitná e-mailová adresa musí byť text.',
            'max'      => 'Univerzitná e-mailová adresa môže mať najviac 255 znakov.',
            'email'    => 'Zadaj platnú univerzitnú e-mailovú adresu.',
            'regex'    => 'Použi tvar meno.priezvisko alebo meno.stredne.priezvisko a doména musí byť student.ukf.sk.',
            'unique'   => 'Táto univerzitná e-mailová adresa je už zaregistrovaná.',
        ],

        'faculty' => [
            'required' => 'Študijný odbor je povinný.',
            'integer'  => 'Študijný odbor musí byť zadaný ako číslo.',
            'exists'   => 'Zvolený študijný odbor neexistuje.',
        ],

        // ==== FIRMA ====
    ],


];
