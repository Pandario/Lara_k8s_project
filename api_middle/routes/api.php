<?php

use Illuminate\Support\Facades\Route;

Route::get('/hello', fn () => response()->json(['msg' => 'Hello from API Middle']));