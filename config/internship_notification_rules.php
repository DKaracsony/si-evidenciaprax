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
