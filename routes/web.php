<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $log = \App\Models\LogMessage::orderBy('id', 'DESC')->first();

    return view('welcome', ['log' => $log]);
});
