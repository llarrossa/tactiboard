<?php

use App\Http\Controllers\BoardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SharedBoardController;
use App\Http\Controllers\SharedLinkController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// RF-015: fora de qualquer grupo de autenticacao, de proposito. O acesso e
// controlado pelo token e pelo estado da prancheta (docs/03 §7.2), nunca por
// sessao — quem abre o link nao tem conta.
Route::get('/share/{token}', [SharedBoardController::class, 'show'])->name('share.show');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // A propriedade das pranchetas (RN-001) é aplicada pela BoardPolicy, presa
    // à rota com `can`. Assim a regra fica visível no route:list e o controller
    // não repete verificação de dono.
    Route::get('/boards/create', [BoardController::class, 'create'])->name('boards.create');
    Route::post('/boards', [BoardController::class, 'store'])->name('boards.store');
    Route::get('/boards/{board}', [BoardController::class, 'show'])->name('boards.show')->can('view', 'board');
    Route::get('/boards/{board}/edit', [BoardController::class, 'edit'])->name('boards.edit')->can('update', 'board');
    Route::put('/boards/{board}', [BoardController::class, 'update'])->name('boards.update')->can('update', 'board');
    Route::delete('/boards/{board}', [BoardController::class, 'destroy'])->name('boards.destroy')->can('delete', 'board');

    Route::post('/boards/{board}/share', [SharedLinkController::class, 'store'])->name('boards.share.store')->can('share', 'board');
    Route::put('/boards/{board}/share', [SharedLinkController::class, 'update'])->name('boards.share.update')->can('share', 'board');
    Route::delete('/boards/{board}/share', [SharedLinkController::class, 'destroy'])->name('boards.share.destroy')->can('share', 'board');
});

require __DIR__.'/auth.php';
