<?php

use Illuminate\Support\Facades\Route;
/** Liveware Components */
use App\Livewire\Dashboard;
use App\Livewire\Login;
use App\Livewire\ManageUsers;
use App\Livewire\ManageIps;
use App\Jobs\ProcessCsvFile;
use Illuminate\Http\Request;
use App\Http\Controllers\FetchFileController;

/** Login */
Route::get('/login', Login::class)->name('login');

Route::group(['middleware' => ['auth']], function () { 
    /** Dashboard */
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::post('/post-csv', [FetchFileController::class, 'save'])->name('post-csv');
    Route::get('/manage-users', ManageUsers::class)->name('manage-users')->middleware('block-users');
    Route::get('/manage-ips', ManageIps::class)->name('manage-ips')->middleware('block-users');
    Route::get('/download/{username}/csv/{folder}/{file}', [FetchFileController::class, 'download'])->name('download');
});

