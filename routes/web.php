<?php

use Illuminate\Support\Facades\Route;

//VUE FRONTEND
Route::get('/{any}', function () {
    return view('index');
})->where('any', '.*');
