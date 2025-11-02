<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompanyActivationController;

// Aktivációs link (NE menjen a SPA alá)
Route::get('/activate/company/{token}', [CompanyActivationController::class, 'activate'])
    ->name('company.activate')
    ->middleware('signed'); // ha temporarySignedRoute-ot használsz

// VUE FRONTEND – ez legyen a legutolsó!
Route::view('/{any}', 'index')->where('any', '.*');
