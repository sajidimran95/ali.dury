<?php

use Illuminate\Support\Facades\Route;
use Modules\AiAssistance\Http\Controllers\ChatController;
use Modules\AiAssistance\Http\Controllers\DashboardController;
use Modules\AiAssistance\Http\Controllers\InstallController;
use Modules\AiAssistance\Http\Controllers\SettingsController;

Route::middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin'])
    ->prefix('ai')
    ->name('ai.')
    ->group(function () {
        Route::get('install', [InstallController::class, 'index'])->name('install');
        Route::post('install', [InstallController::class, 'install']);

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('panel', [DashboardController::class, 'panel'])->name('panel');
        Route::post('chat', [ChatController::class, 'send'])->name('chat.send');
        Route::post('chat/clear', [ChatController::class, 'clear'])->name('chat.clear');

        Route::get('settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
    });
