<?php

use Illuminate\Support\Facades\Route;
use Modules\AiAssistance\Http\Controllers\ChatController;
use Modules\AiAssistance\Http\Controllers\DashboardController;
use Modules\AiAssistance\Http\Controllers\InstallController;
use Modules\AiAssistance\Http\Controllers\InvoiceExtractionController;
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

        Route::post('invoice/extract', [InvoiceExtractionController::class, 'extract'])->name('invoice.extract');
        Route::get('invoice/result/{token}', [InvoiceExtractionController::class, 'result'])->name('invoice.result');

        Route::get('intelligence', [\Modules\AiAssistance\Http\Controllers\IntelligenceController::class, 'index'])->name('intelligence.index');
        Route::get('intelligence/supplier-prices', [\Modules\AiAssistance\Http\Controllers\IntelligenceController::class, 'supplierPrices'])->name('intelligence.supplier-prices');
        Route::post('intelligence/draft-po', [\Modules\AiAssistance\Http\Controllers\IntelligenceController::class, 'draftPo'])->name('intelligence.draft-po');
        Route::post('intelligence/chat', [\Modules\AiAssistance\Http\Controllers\IntelligenceController::class, 'chat'])->name('intelligence.chat');
        Route::post('intelligence/digest', [\Modules\AiAssistance\Http\Controllers\IntelligenceController::class, 'sendDigest'])->name('intelligence.digest');
        Route::post('intelligence/match-review', [\Modules\AiAssistance\Http\Controllers\IntelligenceController::class, 'reviewMatch'])->name('intelligence.match-review');
        Route::get('intelligence/pos-hints', [\Modules\AiAssistance\Http\Controllers\IntelligenceController::class, 'posHints'])->name('intelligence.pos-hints');
        Route::get('intelligence/addons', [\Modules\AiAssistance\Http\Controllers\IntelligenceController::class, 'addons'])->name('intelligence.addons');
        Route::get('intelligence/embed', [\Modules\AiAssistance\Http\Controllers\IntelligenceController::class, 'embed'])->name('intelligence.embed');
    });
