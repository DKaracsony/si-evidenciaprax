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
];
