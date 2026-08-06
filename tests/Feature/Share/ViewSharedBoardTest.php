<?php

use App\Enums\BoardCategory;
use App\Models\Board;
use App\Models\SharedLink;
use App\Models\User;

/**
 * Canvas com um elemento de cada tipo, para conferir que a pagina publica
 * desenha tudo o que o editor grava.
 *
 * @return array<string, mixed>
 */
function sharedCanvas(): array
{
    return ['elements' => [
        ['id' => 'k3Ba9xQ2mZ', 'type' => 'player', 'team' => 'home', 'number' => 9, 'x' => 200, 'y' => 350],
        ['id' => 'p7Tc1vLd4R', 'type' => 'player', 'team' => 'away', 'number' => 4, 'x' => 800, 'y' => 350],
        ['id' => 'b2Nf8sWq0E', 'type' => 'ball', 'x' => 525, 'y' => 340],
        ['id' => 'c5Hj3yUr6T', 'type' => 'cone', 'x' => 300, 'y' => 200],
        ['id' => 't9Zx4mKp1A', 'type' => 'text', 'content' => 'Atacar profundidade', 'x' => 400, 'y' => 200],
        ['id' => 'a1Qw6nJb8S', 'type' => 'arrow', 'start' => ['x' => 200, 'y' => 350], 'end' => ['x' => 300, 'y' => 250]],
    ]];
}

test('um visitante sem conta visualiza a prancheta pelo link', function () {
    $board = Board::factory()->public()->create([
        'title' => 'Saída de bola 4-3-3',
        'description' => 'Construção desde o goleiro.',
        'category' => BoardCategory::Attack,
        'canvas_data' => sharedCanvas(),
    ]);
    $link = SharedLink::factory()->for($board)->create();

    $response = $this->get(route('share.show', $link->token));

    $response->assertOk();
    $response->assertSee('Saída de bola 4-3-3');
    $response->assertSee('Construção desde o goleiro.');
    $response->assertSee(BoardCategory::Attack->label());
    $response->assertSee('Atacar profundidade');
    // O numero do jogador e o texto do elemento provam que o canvas foi
    // desenhado, nao so os metadados.
    $response->assertSee('>9</text>', escape: false);
});

test('a pagina publica nao oferece edicao nem o editor', function () {
    $board = Board::factory()->public()->create(['canvas_data' => sharedCanvas()]);
    $link = SharedLink::factory()->for($board)->create();

    $response = $this->get(route('share.show', $link->token));

    $response->assertOk();
    $response->assertSee(__('View only'));
    $response->assertDontSee(__('Save board'));
    $response->assertDontSee(__('Edit board'));
    $response->assertDontSee(__('Add elements'));
    // Sem Livewire e sem as ligacoes de arrasto do editor.
    $response->assertDontSee('wire:', escape: false);
    $response->assertDontSee('startDrag', escape: false);
    $response->assertDontSee('offsetFor', escape: false);
});

test('um token inexistente nega acesso', function () {
    $this->get(route('share.show', 'token-que-nunca-existiu'))->assertNotFound();
});

test('uma prancheta privada nega acesso mesmo com token valido', function () {
    $link = SharedLink::factory()->for(Board::factory())->create();

    $this->get(route('share.show', $link->token))->assertNotFound();
});

test('um link expirado nega acesso mesmo com a prancheta publica', function () {
    $link = SharedLink::factory()->for(Board::factory()->public())->expired()->create();

    $this->get(route('share.show', $link->token))->assertNotFound();
});

test('revogar o compartilhamento derruba o acesso pelo link', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    $this->actingAs($user)->post(route('boards.share.store', $board));
    $token = $board->sharedLinks()->sole()->token;

    $this->get(route('share.show', $token))->assertOk();

    $this->actingAs($user)->delete(route('boards.share.destroy', $board));

    $this->get(route('share.show', $token))->assertNotFound();
});

test('excluir a prancheta derruba o acesso pelo link', function () {
    $board = Board::factory()->public()->create();
    $link = SharedLink::factory()->for($board)->create();

    $board->delete();

    $this->get(route('share.show', $link->token))->assertNotFound();
});

test('um canvas corrompido nao derruba a pagina publica', function () {
    $board = Board::factory()->public()->create([
        'canvas_data' => ['elements' => [
            ['id' => 'ok', 'type' => 'ball', 'x' => 525, 'y' => 340],
            ['id' => 'quebrado', 'type' => 'disco-voador', 'x' => 10, 'y' => 10],
            ['id' => 'fora', 'type' => 'cone', 'x' => 99999, 'y' => 10],
        ]],
    ]);
    $link = SharedLink::factory()->for($board)->create();

    $this->get(route('share.show', $link->token))->assertOk();
});

test('a pagina publica pede para nao ser indexada', function () {
    $link = SharedLink::factory()->for(Board::factory()->public())->create();

    $this->get(route('share.show', $link->token))->assertSee('noindex', escape: false);
});

test('a prancheta compartilhada continua fechada na area autenticada', function () {
    $board = Board::factory()->public()->create();
    SharedLink::factory()->for($board)->create();

    // Tornar publica libera a leitura pelo token, nunca pela rota autenticada:
    // a BoardPolicy segue valendo para quem tem conta (RN-001).
    $this->actingAs(User::factory()->create())
        ->get(route('boards.show', $board))
        ->assertForbidden();
});
