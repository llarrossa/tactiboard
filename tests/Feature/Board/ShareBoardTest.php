<?php

use App\Enums\BoardVisibility;
use App\Models\Board;
use App\Models\SharedLink;
use App\Models\User;

test('o dono compartilha a prancheta e recebe um link', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('boards.share.store', $board));

    $response->assertRedirect(route('boards.show', $board));
    $response->assertSessionHas('status', 'board-shared');

    $link = $board->sharedLinks()->sole();

    expect($link->token)->toHaveLength(32)
        ->and($link->expires_at)->toBeNull()
        ->and($board->fresh()->visibility)->toBe(BoardVisibility::Public);
});

test('compartilhar de novo reutiliza o mesmo token', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    $this->actingAs($user)->post(route('boards.share.store', $board));
    $token = $board->sharedLinks()->sole()->token;

    $this->actingAs($user)->post(route('boards.share.store', $board));

    expect($board->sharedLinks()->count())->toBe(1)
        ->and($board->sharedLinks()->sole()->token)->toBe($token);
});

test('cada prancheta recebe um token diferente', function () {
    $user = User::factory()->create();
    $first = Board::factory()->for($user)->create();
    $second = Board::factory()->for($user)->create();

    $this->actingAs($user)->post(route('boards.share.store', $first));
    $this->actingAs($user)->post(route('boards.share.store', $second));

    expect($first->sharedLinks()->sole()->token)
        ->not->toBe($second->sharedLinks()->sole()->token);
});

test('deixar de compartilhar torna a prancheta privada sem apagar o link', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->public()->create();
    $link = SharedLink::factory()->for($board)->create();

    $response = $this->actingAs($user)->delete(route('boards.share.destroy', $board));

    $response->assertRedirect(route('boards.show', $board));
    $response->assertSessionHas('status', 'board-unshared');

    // docs/03 §6.2: tornar privada revoga o acesso sem remover os links.
    expect($board->fresh()->visibility)->toBe(BoardVisibility::Private)
        ->and(SharedLink::query()->whereKey($link->id)->exists())->toBeTrue();
});

test('recompartilhar depois de revogar mantem a mesma url', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    $this->actingAs($user)->post(route('boards.share.store', $board));
    $token = $board->sharedLinks()->sole()->token;

    $this->actingAs($user)->delete(route('boards.share.destroy', $board));
    $this->actingAs($user)->post(route('boards.share.store', $board));

    expect($board->sharedLinks()->sole()->token)->toBe($token);
});

test('um usuario nao compartilha a prancheta de outro', function () {
    $board = Board::factory()->create();

    $response = $this->actingAs(User::factory()->create())
        ->post(route('boards.share.store', $board));

    $response->assertForbidden();

    expect($board->sharedLinks()->count())->toBe(0)
        ->and($board->fresh()->visibility)->toBe(BoardVisibility::Private);
});

test('um usuario nao revoga o compartilhamento da prancheta de outro', function () {
    $board = Board::factory()->public()->create();

    $response = $this->actingAs(User::factory()->create())
        ->delete(route('boards.share.destroy', $board));

    $response->assertForbidden();

    expect($board->fresh()->visibility)->toBe(BoardVisibility::Public);
});

test('um visitante nao compartilha prancheta', function () {
    $board = Board::factory()->create();

    $this->post(route('boards.share.store', $board))->assertRedirect(route('login'));
    $this->delete(route('boards.share.destroy', $board))->assertRedirect(route('login'));
});

test('a prancheta privada mostra o convite para compartilhar', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    $response = $this->actingAs($user)->get(route('boards.show', $board));

    $response->assertOk();
    $response->assertSee(__('Share board'));
    $response->assertDontSee(__('Stop sharing'));
});

test('a prancheta compartilhada mostra a url publica', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->public()->create();
    $link = SharedLink::factory()->for($board)->create();

    $response = $this->actingAs($user)->get(route('boards.show', $board));

    $response->assertOk();
    $response->assertSee(route('share.show', $link->token));
    $response->assertSee(__('Stop sharing'));
    $response->assertDontSee(__('Share board'));
});

test('uma prancheta publica sem link ainda oferece compartilhar', function () {
    $user = User::factory()->create();
    // Estado possivel apenas por edicao direta no banco: a interface nunca
    // torna publica sem gerar o token. Mostrar a URL de um link inexistente
    // quebraria a pagina, entao o painel volta ao convite.
    $board = Board::factory()->for($user)->public()->create();

    $response = $this->actingAs($user)->get(route('boards.show', $board));

    $response->assertOk();
    $response->assertSee(__('Share board'));
});
