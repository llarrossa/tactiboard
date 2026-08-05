<?php

use App\Enums\BoardCategory;
use App\Models\Board;
use App\Models\User;

test('o dono visualiza a propria prancheta', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create([
        'title' => 'Saída de bola 4-3-3',
        'description' => 'Construção desde o goleiro.',
        'category' => BoardCategory::Attack,
    ]);

    $response = $this->actingAs($user)->get(route('boards.show', $board));

    $response->assertOk();
    $response->assertSee('Saída de bola 4-3-3');
    $response->assertSee('Construção desde o goleiro.');
    $response->assertSee(BoardCategory::Attack->label());
});

test('um usuario nao visualiza a prancheta de outro', function () {
    $board = Board::factory()->create();

    $response = $this->actingAs(User::factory()->create())
        ->get(route('boards.show', $board));

    $response->assertForbidden();
});

test('um visitante nao visualiza prancheta', function () {
    $board = Board::factory()->create();

    $this->get(route('boards.show', $board))->assertRedirect(route('login'));
});

test('uma prancheta inexistente retorna 404', function () {
    $response = $this->actingAs(User::factory()->create())->get('/boards/999999');

    $response->assertNotFound();
});
