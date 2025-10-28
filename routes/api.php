<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\FacultyController;

Route::get('/faculties', [FacultyController::class, 'index']);


