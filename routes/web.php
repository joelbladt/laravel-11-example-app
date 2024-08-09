<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $log = \App\Models\LogMessage::latest('id')->first() ?? null;

    return view('welcome', ['log' => $log]);
});
