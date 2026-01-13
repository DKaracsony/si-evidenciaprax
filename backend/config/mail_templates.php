<?php

return [
    'company_activation' => [
        'view'    => 'emails.company_activation',
        'subject' => 'Aktivujte svoj firemný účet',
    ],
    'company_temp_password' => [
        'view'    => 'emails.company_temp_password',
        'subject' => 'Dočasné heslo do systému Evidencia praxí',
    ],
    'student_registration' => [
        'view'    => 'emails.student_registration',
        'subject' => 'Dočasné heslo do systému Evidencia praxí',
    ],
    'password_reset' => [
        'view'    => 'emails.password_reset',
        'subject' => 'Obnovenie hesla – Evidencia praxe',
    ],

    'internship_confirmed_to_approved' => [
        'view'    => 'emails.internship_confirmed_to_approved',
        'subject' => 'Prax bola schválená',
    ],

    'internship_approved_to_defended' => [
        'view'    => 'emails.internship_approved_to_defended',
        'subject' => 'Prax bola úspešne obhájená',
    ],

    'internship_approved_to_not_defended' => [
        'view'    => 'emails.internship_approved_to_not_defended',
        'subject' => 'Prax nebola obhájená',
    ],
];
