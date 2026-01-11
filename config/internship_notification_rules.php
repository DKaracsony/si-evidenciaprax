<?php


use App\Models\Role;
use App\Models\Status;

return [
    'statuses' => [
        // keď vyvolá firma notifikáciu
        Status::ACCEPTED => [
            'recipient' => [Role::STUDENT, Role::GARANT],
            'email' => false,
            'notification_text_key'        => 'notification.INTERNSHIP_STATUS_CHANGED_TO_ACCEPTED_STUDENT',
            'notification_text_garant_key' => 'notification.INTERNSHIP_STATUS_CHANGED_TO_ACCEPTED_GARANT',
        ],

        Status::REJECTED => [
            'recipient' => [Role::STUDENT],
            'email' => false,
            'notification_text_key' => 'notification.INTERNSHIP_STATUS_CHANGED_TO_REJECTED',
        ],

        // keď vyvolá garant notifikáciu
        Status::APPROVED => [
            'recipient' => [Role::STUDENT, Role::COMPANY],
            'notification_text_key'         => 'notification.INTERNSHIP_STATUS_CHANGED_TO_APPROVED_STUDENT',
            'notification_text_company_key' => 'notification.INTERNSHIP_STATUS_CHANGED_TO_APPROVED_COMPANY',
            'email' => true,
            'email_key' => 'internship_confirmed_to_approved'
        ],

        // keď vyvolá garant alebo externý systém notifikáciu
        Status::DEFENDED => [
            'recipient' => [Role::STUDENT, Role::COMPANY],
            'notification_text_key'         => 'notification.INTERNSHIP_STATUS_CHANGED_TO_DEFENDED_STUDENT',
            'notification_text_company_key' => 'notification.INTERNSHIP_STATUS_CHANGED_TO_DEFENDED_COMPANY',
            'email' => true,
            'email_key' => 'internship_approved_to_defended'
        ],

        Status::UNDEFENDED => [
            'recipient' => [Role::STUDENT, Role::COMPANY],
            'notification_text_key'         => 'notification.INTERNSHIP_STATUS_CHANGED_TO_UNDEFENDED_STUDENT',
            'notification_text_company_key' => 'notification.INTERNSHIP_STATUS_CHANGED_TO_UNDEFENDED_COMPANY',
            'email' => true,
            'email_key' => 'internship_approved_to_not_defended'
        ],
    ],
];
