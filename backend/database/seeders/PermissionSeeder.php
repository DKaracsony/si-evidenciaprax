<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'profile.view_other', 'description' => 'Umožňuje vidieť všetky profily v rámci systému vrátane všetkých atribútov používateľov', 'created_at' => now()],
            ['name' => 'profile.view_related', 'description' => '	Umožňuje vidieť iba tie profily, ktoré majú ku mne vzťah', 'created_at' => now()],
            ['name' => 'company.search', 'description' => 'Umožňuje vyhľadávať firmy v rámci systému', 'created_at' => now()],
            ['name' => 'practice.create', 'description' => 'Umožňuje vytvoriť odbornú prax', 'created_at' => now()],
            ['name' => 'practice.view_detail_own', 'description' => 'Umožňuje vidieť detaily praxí, ktoré patria ku mne', 'created_at' => now()],
            ['name' => 'practice.view_detail_other', 'description' => 'Umožňuje vidieť detaily všetkých praxí', 'created_at' => now()],
            ['name' => 'practice.generate_agreement_pdf', 'description' => 'Umožňuje generovať dohodu o praxi (PDF)', 'created_at' => now()],
            ['name' => 'practice.update_fields_any', 'description' => 'Umožňuje zmeniť atribúty praxe', 'created_at' => now()],
            ['name' => 'practice.upload_agreement', 'description' => 'Umožňuje nahrať k mojej praxi zmluvu', 'created_at' => now()],
            ['name' => 'practice.upload_statement', 'description' => 'Umožňuje nahrať výkaz o praxi, ktorý patrí ku mne', 'created_at' => now()],
            ['name' => 'practice.approve_statement', 'description' => 'Umožňuje potvrdiť alebo zamietnuť výkaz', 'created_at' => now()],
            ['name' => 'practice.change_status_to_accepted', 'description' => 'Umožňuje zmeniť stav praxe na akceptovaná (alebo zamietnutá)', 'created_at' => now()],
            ['name' => 'practice.change_status_to_approved', 'description' => 'Umožňuje zmeniť stav praxe na schválená', 'created_at' => now()],
            ['name' => 'practice.change_status_to_defended', 'description' => 'Umožňuje zmeniť stav praxe na obhájená', 'created_at' => now()],
            ['name' => 'practice.can_see_all_documents', 'description' => 'Umožňuje vidieť všetky nahrané dokumenty v systéme', 'created_at' => now()],
            ['name' => 'practice.can_see_own_documents', 'description' => 'Umožňuje vidieť dokumenty, ktoré majú väzbu ku mne', 'created_at' => now()],
            ['name' => 'practice.generate_export', 'description' => 'Umožňuje vygenerovať CSV export praxí', 'created_at' => now()],
            ['name' => 'dashboard.view_overview', 'description' => 'Vidím všetky štatistické údaje zaevidovaných praxí', 'created_at' => now()],
            ['name' => 'dashboard.view_own_practices', 'description' => 'Vidím iba svoje zaevidované praxe a ich štatistiky ako stav', 'created_at' => now()],
        ];

        Permission::insert($permissions);
    }
}
