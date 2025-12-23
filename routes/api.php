<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GarantController;
use App\Http\Controllers\RegistrationController;
use App\Models\Status;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FacultyController;
use App\Http\Controllers\CompanyActivationController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\InternshipController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\NotificationController;

// REGISTRATION FORM ENDPOINTS
Route::get('/faculties', [FacultyController::class, 'index']);
Route::post('/register', [RegistrationController::class, 'handleRegister']);
Route::get('/company/activate', [CompanyActivationController::class, 'activate']);
Route::get('/countries', [CountryController::class, 'index']);
Route::post('/company/activate/resend', [CompanyActivationController::class, 'resend']);

// PASSWORD RESET ENDPOINTS
Route::post('/password/forgot', [PasswordResetController::class, 'forgot']);
Route::post('/password/reset', [PasswordResetController::class, 'reset']);

// AUTHENTICATED ENDPOINTS
Route::middleware('auth:api')->group(function () {

    Route::get('/user', [AuthController::class, 'userDetails']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/account/password', [PasswordResetController::class, 'changePassword']);
    Route::get('/academic-years', [AcademicYearController::class, 'index']);

    // Notifikácia
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/seen', [NotificationController::class, 'markAsSeen']);
    Route::patch('/notifications/seen-all', [NotificationController::class, 'markAllAsSeen']);

    Route::get('/company/internships', [InternshipController::class, 'companyCreatedInternships']);

    Route::prefix('student')->group(function () {
        Route::get('/internships', [InternshipController::class, 'index'])->middleware(['permission:practice.view_detail_own']);
        Route::post('/internship', [InternshipController::class, 'store'])->middleware(['permission:practice.create']);
        Route::get('/internship-detail/{id}', [InternshipController::class, 'show'])->middleware(['permission:practice.view_detail_own']);
        Route::get('/internship-detail/{internship}/agreement-pdf', [InternshipController::class, 'downloadAgreementPdf'])->middleware(['permission:practice.generate_agreement_pdf']);
    });

    Route::get('/companies/search', [CompanyController::class, 'searchByName'])->middleware(['permission:company.search']);
    Route::get('/companies/{company}', [CompanyController::class, 'show']);

    Route::prefix('garant')->group(function () {
        Route::get('/my-faculties', [GarantController::class, 'getMyFaculties']);
        Route::post('/save-my-faculties', [GarantController::class, 'saveMyFaculties']);
    });

    Route::prefix('internship')->group(function () {
        Route::get('/all', [InternshipController::class, 'allInternshipsWithPaginationAndFilter'])->middleware(['permission:practice.view_detail_other']);
    });

    Route::prefix('internship/change-status')->group(function () {
        Route::post('/acceptance', [InternshipController::class, 'changeStatus'])->middleware(['permission:practice.change_status_to_accepted'])->defaults('to', 'acceptance'); // POTVRDENIE
        Route::post('/approval',   [InternshipController::class, 'changeStatus'])->middleware(['permission:practice.change_status_to_approved'])->defaults('to', 'approval'); // SCHVÁLENIE
        Route::post('/defense',    [InternshipController::class, 'changeStatus'])->middleware(['permission:practice.change_status_to_defended'])->defaults('to', 'defense');  // OBHÁJENIE
    });
});
