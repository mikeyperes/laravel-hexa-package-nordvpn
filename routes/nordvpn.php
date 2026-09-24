<?php

use hexa_package_nordvpn\Http\Controllers\NordVpnController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'locked', 'system_lock', 'two_factor', 'role'])->group(function () {
    Route::get('/nordvpn/settings', [NordVpnController::class, 'settings'])->name('nordvpn.settings');
    Route::post('/nordvpn/settings', [NordVpnController::class, 'saveSettings'])->name('nordvpn.settings.save');
    Route::post('/nordvpn/test', [NordVpnController::class, 'test'])->name('nordvpn.test');
});
