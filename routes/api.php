<?php

use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FacultyController;
use App\Http\Controllers\CompanyActivationController;
use App\Http\Controllers\CountryController;


//REGISTRATION FORM ENDPOINTS
Route::get('/faculties', [FacultyController::class, 'index']);
Route::post('/register', [RegistrationController::class, 'handleRegister']);
Route::get('/company/activate', [CompanyActivationController::class, 'activate']);
Route::get('/countries', [CountryController::class, 'index']);

