<?php

use App\Models\Board;
use App\Models\User;

test('o dashboard lista as pranchetas do usuario', function () {
    $user = User::factory()->create();
    Board::factory()->for($user)->create(['title' => 'Saída de bola 4-3-3']);
    Board::factory()->for($user)->create(['title' => 'Pressão alta']);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
    $response->assertSee('Saída de bola 4-3-3');
    $response->assertSee('Pressão alta');
});

test('o dashboard nao mostra prancheta de outro usuario', function () {
    $user = User::factory()->create();
    $outroUsuario = User::factory()->create();

    Board::factory()->for($user)->create(['title' => 'Minha jogada']);
    Board::factory()->for($outroUsuario)->create(['title' => 'Jogada alheia']);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertSee('Minha jogada');
    $response->assertDontSee('Jogada alheia');
});

test('o dashboard mostra o estado vazio quando nao ha prancheta', function () {
    $response = $this->actingAs(User::factory()->create())->get('/dashboard');

    $response->assertOk();
    $response->assertSee(__('You have no boards yet.'));
});

test('as pranchetas aparecem da mais recente para a mais antiga', function () {
    $user = User::factory()->create();

    Board::factory()->for($user)->create([
        'title' => 'Prancheta antiga',
        'created_at' => now()->subDays(2),
    ]);
    Board::factory()->for($user)->create([
        'title' => 'Prancheta recente',
        'created_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertSeeInOrder(['Prancheta recente', 'Prancheta antiga']);
});

test('a listagem e paginada', function () {
    $user = User::factory()->create();
    Board::factory()->for($user)->count(15)->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk();
    expect($response->viewData('boards')->count())->toBe(12)
        ->and($response->viewData('boards')->total())->toBe(15);
});

test('um visitante nao acessa o dashboard', function () {
    $this->get('/dashboard')->assertRedirect(route('login'));
});
