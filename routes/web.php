<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/api/v1/docs', function () {
    return view('swagger');
});

Route::get('/openapi.json', function () {
    $path = public_path('openapi.json');
    if (! file_exists($path)) {
        abort(404);
    }

    return response()->json(json_decode(file_get_contents($path), true));
});
