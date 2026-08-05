<?php

use App\Enums\BoardCategory;
use App\Models\Board;
use App\Models\User;

test('o dono abre a tela de edicao', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    $response = $this->actingAs($user)->get(route('boards.edit', $board));

    $response->assertOk();
    $response->assertSee($board->title);
});

test('o dono edita a propria prancheta', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create([
        'title' => 'Título antigo',
        'category' => BoardCategory::Other,
    ]);

    $response = $this->actingAs($user)->put(route('boards.update', $board), [
        'title' => 'Título novo',
        'description' => 'Descrição nova.',
        'category' => BoardCategory::Training->value,
    ]);

    $response->assertRedirect(route('boards.show', $board));

    $board->refresh();

    expect($board->title)->toBe('Título novo')
        ->and($board->description)->toBe('Descrição nova.')
        ->and($board->category)->toBe(BoardCategory::Training);
});

test('a edicao nao altera o dono da prancheta', function () {
    $user = User::factory()->create();
    $outroUsuario = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    $this->actingAs($user)->put(route('boards.update', $board), [
        'title' => 'Título novo',
        'category' => BoardCategory::Other->value,
        'user_id' => $outroUsuario->id,
    ]);

    expect($board->refresh()->user_id)->toBe($user->id);
});

test('a edicao preserva o canvas', function () {
    $user = User::factory()->create();
    $canvas = ['elements' => [['type' => 'player', 'team' => 'home', 'number' => 9, 'x' => 200, 'y' => 350]]];
    $board = Board::factory()->for($user)->create(['canvas_data' => $canvas]);

    $this->actingAs($user)->put(route('boards.update', $board), [
        'title' => 'Título novo',
        'category' => BoardCategory::Other->value,
    ]);

    // toEqual, nao toBe: o MySQL normaliza a ordem das chaves em coluna JSON,
    // entao o que volta tem o mesmo conteudo em outra ordem.
    expect($board->refresh()->canvas_data)->toEqual($canvas);
});

test('a edicao exige nome e categoria', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create(['title' => 'Título original']);

    $response = $this->actingAs($user)
        ->from(route('boards.edit', $board))
        ->put(route('boards.update', $board), ['title' => '', 'category' => '']);

    $response->assertSessionHasErrors(['title', 'category']);
    expect($board->refresh()->title)->toBe('Título original');
});

test('um usuario nao edita a prancheta de outro', function () {
    $board = Board::factory()->create(['title' => 'Título original']);
    $intruso = User::factory()->create();

    $this->actingAs($intruso)->get(route('boards.edit', $board))->assertForbidden();

    $this->actingAs($intruso)->put(route('boards.update', $board), [
        'title' => 'Invadido',
        'category' => BoardCategory::Other->value,
    ])->assertForbidden();

    expect($board->refresh()->title)->toBe('Título original');
});

test('um visitante nao edita prancheta', function () {
    $board = Board::factory()->create(['title' => 'Título original']);

    $this->get(route('boards.edit', $board))->assertRedirect(route('login'));
    $this->put(route('boards.update', $board), [
        'title' => 'Invadido',
        'category' => BoardCategory::Other->value,
    ])->assertRedirect(route('login'));

    expect($board->refresh()->title)->toBe('Título original');
});
