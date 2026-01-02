<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'E-Commerce API',
        'documentation' => '/api/documentation',
    ]);
});
