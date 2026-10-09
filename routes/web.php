<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MarketplaceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketplaceController::class, 'index'])->name('home');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->middleware('throttle:6,1');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'store'])->middleware('throttle:6,1');
});
Route::middleware('auth')->group(function () {
    Route::get('/messages', [ConversationController::class, 'index'])->name('messages.index');
    Route::post('/listings/{listing}/messages', [ConversationController::class, 'store'])->middleware('throttle:10,1')->name('messages.start');
    Route::get('/messages/{conversation}', [ConversationController::class, 'show'])->name('messages.show');
    Route::post('/messages/{conversation}', [ConversationController::class, 'reply'])->middleware('throttle:30,1')->name('messages.reply');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [MarketplaceController::class, 'dashboard'])->name('dashboard');
    Route::get('/listings/create', [MarketplaceController::class, 'create'])->name('listings.create');
    Route::post('/listings', [MarketplaceController::class, 'store'])->name('listings.store');
    Route::get('/listings/{listing}/edit', [MarketplaceController::class, 'edit'])->name('listings.edit');
    Route::put('/listings/{listing}', [MarketplaceController::class, 'update'])->name('listings.update');
    Route::delete('/listings/{listing}', [MarketplaceController::class, 'destroy'])->name('listings.destroy');
});
Route::get('/listings/{listing}', [MarketplaceController::class, 'show'])->name('listings.show');
