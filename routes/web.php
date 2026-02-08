<?php

use Illuminate\Support\Facades\Route;

Route::get('/sanctum/csrf-cookie', fn() => response()->noContent());
