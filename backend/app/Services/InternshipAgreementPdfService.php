<?php

namespace App\Services;

use App\Models\Internship;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CompanyOwnerProfile;

class InternshipAgreementPdfService
{
    public function generateFor(Internship $internship): string
    {
        $internship->loadMissing([
            'studentProfile.user',
            'studentProfile.address.country',
            'studentProfile.faculty',
            'company.address.country',
            'academicYear',
        ]);

        $companyOwnerProfile = CompanyOwnerProfile::where('company_id', $internship->company_id)
            ->where('is_active', 1)
            ->first();

        if (!$companyOwnerProfile) {
            throw new \RuntimeException(
                'Nebol nájdený žiadny aktívny profil vlastníka spoločnosti pre spoločnosť ' . $internship->company_id
            );
        }

        $companyOwnerUser = $companyOwnerProfile->user;

        $data = [
            'internship'          => $internship,
            'studentProfile'      => $internship->studentProfile,
            'studentUser'         => $internship->studentProfile?->user,
            'company'             => $internship->company,
            'academicYear'        => $internship->academicYear,
            'companyOwnerUser'    => $companyOwnerUser,
            'companyOwnerProfile' => $companyOwnerProfile,
        ];

        $pdf = Pdf::loadView('pdf.dohoda_praxe', $data);

        return $pdf->output();
    }
}
