<?php


use App\Models\Role;
use App\Models\Status;

return [
    'statuses' => [
        //ked vyvola firma notifikaciu
        Status::ACCEPTED => [
            'recipient' => [Role::STUDENT, Role::GARANT],
            'email' => false,
            'notification_text_key'         => 'notification.INTERNSHIP_STATUS_CHANGED_TO_ACCEPTED_STUDENT',
            'notification_text_garant_key'  => 'notification.INTERNSHIP_STATUS_CHANGED_TO_ACCEPTED_GARANT',
        ],

        Status::REJECTED => [
            'recipient' => [Role::STUDENT],
            'email' => false,
            'notification_text_key' => 'notification.INTERNSHIP_STATUS_CHANGED_TO_REJECTED',
        ],

        //ked vyvola garant notifikaciu
        Status::APPROVED => [
            'recipient' => [Role::STUDENT, Role::COMPANY],
            'notification_text_key'         => 'notification.INTERNSHIP_STATUS_CHANGED_TO_APPROVED_STUDENT',
            'notification_text_company_key' => 'notification.INTERNSHIP_STATUS_CHANGED_TO_APPROVED_COMPANY',
            'email' => true,
        ],

        //ked vyvola garant alebo externy system notifikaciu
        Status::DEFENDED => [
            'recipient' => [Role::STUDENT, Role::COMPANY],
            'notification_text_key'         => 'notification.INTERNSHIP_STATUS_CHANGED_TO_DEFENDED_STUDENT',
            'notification_text_company_key' => 'notification.INTERNSHIP_STATUS_CHANGED_TO_DEFENDED_COMPANY',
            'email' => true,
        ],

        Status::UNDEFENDED => [
            'recipient' => [Role::STUDENT, Role::COMPANY],
            'notification_text_key'         => 'notification.INTERNSHIP_STATUS_CHANGED_TO_UNDEFENDED_STUDENT',
            'notification_text_company_key' => 'notification.INTERNSHIP_STATUS_CHANGED_TO_UNDEFENDED_COMPANY',
            'email' => true,
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | FR-07 – Status transition rules (Garant workflow)
    |--------------------------------------------------------------------------
    */
    'status_transitions' => [

        'Vytvorená' => [
            'Potvrdená' => [
                'requires_explanation' => false,
                'send_email' => false,
            ],
            'Zamietnutá' => [
                'requires_explanation' => true,
                'send_email' => false,
            ],
        ],

        'Potvrdená' => [
            'Schválená' => [
                'requires_explanation' => false,
                'send_email' => true,
                'email_type' => 'confirmed_to_approved',
            ],
        ],

        'Schválená' => [
            'Obhájená' => [
                'requires_explanation' => false,
                'send_email' => true,
                'email_type' => 'approved_to_defended',
            ],
            'Neobhájená' => [
                'requires_explanation' => true,
                'send_email' => true,
                'email_type' => 'approved_to_not_defended',
            ],
        ],
    ],
];
