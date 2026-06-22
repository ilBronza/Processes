<?php

use IlBronza\Processes\Processes;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => config('processes.middleware', ['web', 'auth', 'processes.roles']),
    'prefix' => config('processes.route_prefix', 'processes-management'),
    'as' => config('processes.routePrefix'),
], function () {
    Route::group(['prefix' => 'processes'], function () {
        Route::get('', [Processes::getController('process', 'index'), 'index'])->name('processes.index');
        Route::get('create', [Processes::getController('process', 'create'), 'create'])->name('processes.create');
        Route::post('', [Processes::getController('process', 'store'), 'store'])->name('processes.store');
        Route::get('{process}', [Processes::getController('process', 'show'), 'show'])->name('processes.show');
        Route::get('{process}/edit', [Processes::getController('process', 'edit'), 'edit'])->name('processes.edit');
        Route::put('{process}', [Processes::getController('process', 'update'), 'update'])->name('processes.update');
        Route::delete('{process}/delete', [Processes::getController('process', 'destroy'), 'destroy'])->name('processes.destroy');
    });

    Route::group(['prefix' => 'types'], function () {
        Route::get('', [Processes::getController('processType', 'index'), 'index'])->name('processTypes.index');
        Route::get('create', [Processes::getController('processType', 'create'), 'create'])->name('processTypes.create');
        Route::post('', [Processes::getController('processType', 'store'), 'store'])->name('processTypes.store');
        Route::get('{processType}', [Processes::getController('processType', 'show'), 'show'])->name('processTypes.show');
        Route::get('{processType}/edit', [Processes::getController('processType', 'edit'), 'edit'])->name('processTypes.edit');
        Route::put('{processType}', [Processes::getController('processType', 'update'), 'update'])->name('processTypes.update');
        Route::delete('{processType}/delete', [Processes::getController('processType', 'destroy'), 'destroy'])->name('processTypes.destroy');
    });

    Route::group(['prefix' => 'statuses'], function () {
        Route::get('', [Processes::getController('processStatus', 'index'), 'index'])->name('processStatuses.index');
        Route::get('create', [Processes::getController('processStatus', 'create'), 'create'])->name('processStatuses.create');
        Route::post('', [Processes::getController('processStatus', 'store'), 'store'])->name('processStatuses.store');
        Route::get('{processStatus}', [Processes::getController('processStatus', 'show'), 'show'])->name('processStatuses.show');
        Route::get('{processStatus}/edit', [Processes::getController('processStatus', 'edit'), 'edit'])->name('processStatuses.edit');
        Route::put('{processStatus}', [Processes::getController('processStatus', 'update'), 'update'])->name('processStatuses.update');
        Route::delete('{processStatus}/delete', [Processes::getController('processStatus', 'destroy'), 'destroy'])->name('processStatuses.destroy');
    });

    Route::group(['prefix' => 'status-logs'], function () {
        Route::get('', [Processes::getController('processStatusLog', 'index'), 'index'])->name('processStatusLogs.index');
        Route::get('{processStatusLog}', [Processes::getController('processStatusLog', 'show'), 'show'])->name('processStatusLogs.show');
    });

    Route::group(['prefix' => 'status-transitions'], function () {
        Route::get('', [Processes::getController('processStatusTransition', 'index'), 'index'])->name('processStatusTransitions.index');
        Route::get('create', [Processes::getController('processStatusTransition', 'create'), 'create'])->name('processStatusTransitions.create');
        Route::post('', [Processes::getController('processStatusTransition', 'store'), 'store'])->name('processStatusTransitions.store');
        Route::get('{processStatusTransition}', [Processes::getController('processStatusTransition', 'show'), 'show'])->name('processStatusTransitions.show');
        Route::get('{processStatusTransition}/edit', [Processes::getController('processStatusTransition', 'edit'), 'edit'])->name('processStatusTransitions.edit');
        Route::put('{processStatusTransition}', [Processes::getController('processStatusTransition', 'update'), 'update'])->name('processStatusTransitions.update');
        Route::delete('{processStatusTransition}/delete', [Processes::getController('processStatusTransition', 'destroy'), 'destroy'])->name('processStatusTransitions.destroy');
    });

    Route::group(['prefix' => 'type-fields'], function () {
        Route::get('', [Processes::getController('processTypeField', 'index'), 'index'])->name('processTypeFields.index');
        Route::get('create', [Processes::getController('processTypeField', 'create'), 'create'])->name('processTypeFields.create');
        Route::post('', [Processes::getController('processTypeField', 'store'), 'store'])->name('processTypeFields.store');
        Route::get('{processTypeField}', [Processes::getController('processTypeField', 'show'), 'show'])->name('processTypeFields.show');
        Route::get('{processTypeField}/edit', [Processes::getController('processTypeField', 'edit'), 'edit'])->name('processTypeFields.edit');
        Route::put('{processTypeField}', [Processes::getController('processTypeField', 'update'), 'update'])->name('processTypeFields.update');
        Route::delete('{processTypeField}/delete', [Processes::getController('processTypeField', 'destroy'), 'destroy'])->name('processTypeFields.destroy');
    });
});
