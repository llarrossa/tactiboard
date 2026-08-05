<?php

use App\Enums\BoardCategory;
use App\Enums\BoardVisibility;
use App\Models\Board;
use App\Models\User;

test('a tela de criacao e exibida', function () {
    $response = $this->actingAs(User::factory()->create())->get('/boards/create');

    $response->assertOk();
});

test('um usuario cria uma prancheta', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/boards', [
        'title' => 'Saída de bola 4-3-3',
        'description' => 'Construção desde o goleiro.',
        'category' => BoardCategory::Attack->value,
    ]);

    $board = Board::firstWhere('title', 'Saída de bola 4-3-3');

    expect($board)->not->toBeNull()
        ->and($board->description)->toBe('Construção desde o goleiro.')
        ->and($board->category)->toBe(BoardCategory::Attack);

    $response->assertRedirect(route('boards.show', $board));
});

test('a prancheta pertence a quem a criou', function () {
    $user = User::factory()->create();
    $outroUsuario = User::factory()->create();

    // user_id nao esta no fillable: mesmo enviado, a propriedade vem do
    // usuario autenticado.
    $this->actingAs($user)->post('/boards', [
        'title' => 'Pressão alta',
        'category' => BoardCategory::Defense->value,
        'user_id' => $outroUsuario->id,
    ]);

    expect(Board::firstWhere('title', 'Pressão alta')->user_id)->toBe($user->id);
});

test('uma prancheta nova nasce privada e com o canvas vazio', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/boards', [
        'title' => 'Escanteio ofensivo',
        'category' => BoardCategory::SetPiece->value,
    ]);

    $board = Board::firstWhere('title', 'Escanteio ofensivo');

    expect($board->visibility)->toBe(BoardVisibility::Private)
        ->and($board->canvas_data)->toBe(['elements' => []]);
});

test('a descricao e opcional', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/boards', [
        'title' => 'Bloco baixo',
        'category' => BoardCategory::Defense->value,
    ]);

    $response->assertSessionHasNoErrors();
    expect(Board::firstWhere('title', 'Bloco baixo')->description)->toBeNull();
});

test('a criacao exige nome e categoria', function () {
    $response = $this->actingAs(User::factory()->create())->post('/boards', []);

    $response->assertSessionHasErrors(['title', 'category']);
    expect(Board::count())->toBe(0);
});

test('a criacao recusa categoria fora da lista', function () {
    $response = $this->actingAs(User::factory()->create())->post('/boards', [
        'title' => 'Jogada qualquer',
        'category' => 'categoria-inexistente',
    ]);

    $response->assertSessionHasErrors('category');
    expect(Board::count())->toBe(0);
});

test('um visitante nao cria prancheta', function () {
    $this->get('/boards/create')->assertRedirect(route('login'));

    $this->post('/boards', [
        'title' => 'Jogada qualquer',
        'category' => BoardCategory::Other->value,
    ])->assertRedirect(route('login'));

    expect(Board::count())->toBe(0);
});
