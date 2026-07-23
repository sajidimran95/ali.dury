<?php

use Illuminate\Support\Facades\Route;
use Modules\Crm\Http\Controllers\CallLogController;
use Modules\Crm\Http\Controllers\ConvertLeadController;
use Modules\Crm\Http\Controllers\CrmSettingsController;
use Modules\Crm\Http\Controllers\DashboardController;
use Modules\Crm\Http\Controllers\InstallController;
use Modules\Crm\Http\Controllers\LeadController;
use Modules\Crm\Http\Controllers\PipelineController;
use Modules\Crm\Http\Controllers\ScheduleController;

Route::middleware(['setData', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin'])
    ->prefix('crm')
    ->name('crm.')
    ->group(function () {
        Route::get('install', [InstallController::class, 'index'])->name('install');
        Route::post('install', [InstallController::class, 'install']);

        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('leads/create', [LeadController::class, 'create'])->name('leads.create');
        Route::post('leads', [LeadController::class, 'store'])->name('leads.store');
        Route::get('leads/{id}', [LeadController::class, 'show'])->name('leads.show');
        Route::get('leads/{id}/edit', [LeadController::class, 'edit'])->name('leads.edit');
        Route::put('leads/{id}', [LeadController::class, 'update'])->name('leads.update');
        Route::delete('leads/{id}', [LeadController::class, 'destroy'])->name('leads.destroy');
        Route::post('leads/{id}/convert', [ConvertLeadController::class, 'convert'])->name('leads.convert');

        Route::get('pipeline', [PipelineController::class, 'index'])->name('pipeline.index');
        Route::post('pipeline/{id}/stage', [PipelineController::class, 'updateStage'])->name('pipeline.stage');

        Route::get('schedules', [ScheduleController::class, 'index'])->name('schedules.index');
        Route::post('schedules', [ScheduleController::class, 'store'])->name('schedules.store');
        Route::post('schedules/{id}/complete', [ScheduleController::class, 'complete'])->name('schedules.complete');
        Route::delete('schedules/{id}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

        Route::get('call-logs', [CallLogController::class, 'index'])->name('call-logs.index');
        Route::post('call-logs', [CallLogController::class, 'store'])->name('call-logs.store');
        Route::delete('call-logs/{id}', [CallLogController::class, 'destroy'])->name('call-logs.destroy');

        Route::get('settings', [CrmSettingsController::class, 'index'])->name('settings');
        Route::post('settings', [CrmSettingsController::class, 'update'])->name('settings.update');
    });
