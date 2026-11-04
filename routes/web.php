<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\DraftController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [ContentController::class, 'index'])->name('contents.index');
    Route::get('/contents/create', [ContentController::class, 'create'])->name('contents.create');
    Route::post('/contents', [ContentController::class, 'store'])->name('contents.store');
    Route::get('/contents/{content}', [ContentController::class, 'show'])->name('contents.show');
    Route::put('/contents/{content}', [ContentController::class, 'update'])->name('contents.update');
    Route::put('/contents/{content}/caption', [ContentController::class, 'caption'])->name('contents.caption');
    Route::post('/contents/{content}/approve', [ContentController::class, 'approve'])->name('contents.approve');
    Route::post('/contents/{content}/reopen', [ContentController::class, 'reopen'])->name('contents.reopen');
    Route::post('/contents/{content}/schedule', [ContentController::class, 'schedule'])->name('contents.schedule');
    Route::post('/contents/{content}/publish', [ContentController::class, 'publish'])->name('contents.publish');
    Route::get('/contents/{content}/final', [ContentController::class, 'downloadFinal'])->name('contents.final');

    Route::post('/contents/{content}/assets', [AssetController::class, 'store'])->name('contents.assets.store');
    Route::delete('/assets/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');
    Route::get('/assets/{asset}/download', [AssetController::class, 'download'])->name('assets.download');
    Route::delete('/contents/{content}/assets', [AssetController::class, 'bulkDestroy'])->name('contents.assets.bulk');
    Route::delete('/contents/{content}', [ContentController::class, 'destroy'])->name('contents.destroy');
    Route::delete('/drafts/{draft}', [DraftController::class, 'destroy'])->name('drafts.destroy');
    Route::post('/contents/{content}/drafts', [DraftController::class, 'store'])->name('drafts.store');
    Route::post('/contents/{content}/comments', [DraftController::class, 'comment'])->name('contents.comments.store');
    Route::get('/drafts/{draft}/download', [DraftController::class, 'download'])->name('drafts.download');

    // hanya Super Admin
    Route::prefix('admin')->name('admin.')->middleware(EnsureRole::class . ':super_admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::put('/users/{user}/password', [UserController::class, 'password'])->name('users.password');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});