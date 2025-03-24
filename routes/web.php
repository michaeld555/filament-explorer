<?php

use Illuminate\Support\Facades\Route;
use Michaeld555\FilamentExplorer\Http\Controllers\ExplorerController;

Route::get('explorer/file', [ExplorerController::class, 'file'])->middleware(['web', 'auth:web']);
