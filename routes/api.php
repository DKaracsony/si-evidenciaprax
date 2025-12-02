<?php

use App\Http\Controllers\AuthController;
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

//REGISTRATION FORM ENDPOINTS
Route::get('/faculties', [FacultyController::class, 'index']);
Route::post('/register', [RegistrationController::class, 'handleRegister']);
Route::get('/company/activate', [CompanyActivationController::class, 'activate']);
Route::get('/countries', [CountryController::class, 'index']);
Route::post('/company/activate/resend', [CompanyActivationController::class, 'resend']);

// PASSWORD RESET ENDPOINTS
Route::post('/password/forgot', [PasswordResetController::class, 'forgot']);
Route::post('/password/reset', [PasswordResetController::class, 'reset']);

//AUTHENTICATED ENDPOINTS
Route::middleware('auth:api')->group(function () {
    Route::get('/user', [AuthController::class, 'userDetails']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/account/password', [PasswordResetController::class, 'changePassword']);
    Route::get('/academic-years', [AcademicYearController::class, 'index']);

    Route::prefix('student')->group(function () {
        Route::get('/internships', [InternshipController::class, 'index'])->middleware(['permission:practice.view_detail_own']);
        Route::post('/internship', [InternshipController::class, 'store'])->middleware(['permission:practice.create']);
        Route::get('/internship-detail/{id}', [InternshipController::class, 'show'])->middleware(['permission:practice.view_detail_own']);
        Route::get('/internship-detail/{internship}/agreement-pdf', [InternshipController::class, 'downloadAgreementPdf'])->middleware(['permission:practice.generate_agreement_pdf']);
    });

    Route::get('/companies/search', [CompanyController::class, 'searchByName'])->middleware(['permission:company.search']);

    Route::prefix('internship/change-status')->group(function () {
        Route::post('/acceptance', [InternshipController::class, 'changeStatus'])->middleware(['permission:practice.change_status_to_accepted'])->defaults('to', 'acceptance');
        // TODO: neskor sem doplnit dalsie statusy, bude iba jedna metoda + middleware riesi ci dany user moze menit dany status
    });
});
