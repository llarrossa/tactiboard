<?php

use App\Enums\BoardVisibility;
use App\Models\Board;
use App\Models\SharedLink;
use App\Models\User;

/**
 * Gerar novo link (Fase 5).
 *
 * Fecha a limitacao registrada em docs/03 §7.1: compartilhar de novo
 * reaproveita o token, entao um link que vaze continua o mesmo. Aqui o
 * endereco antigo e aposentado de verdade.
 */
function sharedBoard(?User $owner = null): Board
{
    $owner ??= User::factory()->create();

    $board = Board::factory()->for($owner)->create(['visibility' => BoardVisibility::Public]);

    SharedLink::factory()->for($board)->create(['token' => 'token-antigo-da-prancheta']);

    return $board;
}

test('o dono gera um link novo para a prancheta', function () {
    $board = sharedBoard();

    $this->actingAs($board->user)
        ->put(route('boards.share.update', $board))
        ->assertRedirect(route('boards.show', $board))
        ->assertSessionHas('status', 'board-link-rotated');

    expect($board->sharedLinks()->first()->token)->not->toBe('token-antigo-da-prancheta');
});

test('o endereco antigo para de abrir', function () {
    $board = sharedBoard();

    $this->actingAs($board->user)->put(route('boards.share.update', $board));

    $this->get(route('share.show', 'token-antigo-da-prancheta'))->assertNotFound();
});

test('o endereco novo abre a prancheta', function () {
    $board = sharedBoard();

    $this->actingAs($board->user)->put(route('boards.share.update', $board));

    auth()->logout();

    $this->get(route('share.show', $board->sharedLinks()->first()->token))
        ->assertOk()
        ->assertSee($board->title);
});

test('gerar um link novo nao cria um segundo link', function () {
    // Dois links validos para a mesma prancheta deixariam o dono com uma URL
    // publica que ele nao ve no painel e nao sabe revogar.
    $board = sharedBoard();

    $this->actingAs($board->user)->put(route('boards.share.update', $board));
    $this->put(route('boards.share.update', $board));

    expect($board->sharedLinks()->count())->toBe(1);
});

test('gerar um link novo nao mexe na visibilidade', function () {
    // Os dois mecanismos seguem separados (docs/03 §6.2): aqui muda por onde
    // o acesso acontece, nao se ele pode acontecer.
    $board = sharedBoard();

    $this->actingAs($board->user)->put(route('boards.share.update', $board));

    expect($board->fresh()->visibility)->toBe(BoardVisibility::Public);
});

test('o token novo tem o mesmo formato do primeiro', function () {
    $board = sharedBoard();

    $this->actingAs($board->user)->put(route('boards.share.update', $board));

    expect($board->sharedLinks()->first()->token)->toHaveLength(32)
        ->toMatch('/^[A-Za-z0-9]+$/');
});

test('cada geracao devolve um token diferente', function () {
    $board = sharedBoard();
    $tokens = [];

    foreach (range(1, 3) as $ignored) {
        $this->actingAs($board->user)->put(route('boards.share.update', $board));

        $tokens[] = $board->sharedLinks()->first()->token;
    }

    expect(array_unique($tokens))->toHaveCount(3);
});

test('um usuario nao gera link novo na prancheta de outro', function () {
    $board = sharedBoard();

    $this->actingAs(User::factory()->create())
        ->put(route('boards.share.update', $board))
        ->assertForbidden();

    expect($board->sharedLinks()->first()->token)->toBe('token-antigo-da-prancheta');
});

test('um visitante sem conta nao gera link novo', function () {
    $board = sharedBoard();

    $this->put(route('boards.share.update', $board))->assertRedirect(route('login'));

    expect($board->sharedLinks()->first()->token)->toBe('token-antigo-da-prancheta');
});

test('o painel oferece gerar novo link so quando a prancheta esta compartilhada', function () {
    $board = sharedBoard();

    $this->actingAs($board->user)
        ->get(route('boards.show', $board))
        ->assertSee(__('Generate a new link'))
        ->assertSee(__('Generate a new link?'));

    $board->update(['visibility' => BoardVisibility::Private]);

    $this->get(route('boards.show', $board))
        ->assertDontSee(__('Generate a new link'));
});

test('gerar link novo numa prancheta sem link responde 404', function () {
    // Nao ha endereco a aposentar, e criar um aqui gravaria um token antes de
    // o dono pedir para compartilhar.
    $owner = User::factory()->create();
    $board = Board::factory()->for($owner)->create();

    $this->actingAs($owner)
        ->put(route('boards.share.update', $board))
        ->assertNotFound();

    expect($board->sharedLinks()->count())->toBe(0);
});
