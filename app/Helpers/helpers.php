<?php


if (!function_exists('getAgreementPath')) {
    function getAgreementPath($internship_id, $file_name)
    {
        return storage_path("app/private/internship_agreement/{$internship_id}/{$file_name}");
    }
}

