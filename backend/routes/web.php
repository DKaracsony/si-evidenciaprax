<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return response()->json(['API status' => 'OK']);
})->where('any', '.*');
