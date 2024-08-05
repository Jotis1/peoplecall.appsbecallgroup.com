<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;

// Vamos a hacer un comando que resetee el valor de "executed_request" a 0
// en la tabla "users" mensualmente.

Schedule::call(function () {
    Log::info('Restaurando el valor de "executed_requests" a 0 en la tabla "users".');
    DB::table('users')->update(['executed_requests' => 0]);
})->monthly();