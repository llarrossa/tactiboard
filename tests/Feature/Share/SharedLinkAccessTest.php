<?php

use App\Models\Board;
use App\Models\SharedLink;
use Illuminate\Database\QueryException;

/**
 * As tres condicoes de docs/03 §7.2 vivem no scope accessible(). Este teste
 * cobre as combinacoes: falhar em qualquer uma delas nao pode dar acesso.
 */
test('o scope encontra o link de uma prancheta publica sem expiracao', function () {
    $link = SharedLink::factory()->for(Board::factory()->public())->create();

    expect(SharedLink::query()->accessible()->pluck('id')->all())->toContain($link->id);
});

test('o scope encontra o link cuja expiracao ainda esta no futuro', function () {
    $link = SharedLink::factory()
        ->for(Board::factory()->public())
        ->expiringAt(now()->addDay())
        ->create();

    expect(SharedLink::query()->accessible()->pluck('id')->all())->toContain($link->id);
});

test('o scope ignora o link de uma prancheta privada', function () {
    SharedLink::factory()->for(Board::factory())->create();

    expect(SharedLink::query()->accessible()->count())->toBe(0);
});

test('o scope ignora o link expirado mesmo com a prancheta publica', function () {
    SharedLink::factory()->for(Board::factory()->public())->expired()->create();

    expect(SharedLink::query()->accessible()->count())->toBe(0);
});

test('o scope ignora o link expirado de prancheta privada', function () {
    SharedLink::factory()->for(Board::factory())->expired()->create();

    expect(SharedLink::query()->accessible()->count())->toBe(0);
});

test('excluir a prancheta remove os links dela', function () {
    $board = Board::factory()->public()->create();
    SharedLink::factory()->for($board)->create();

    $board->delete();

    expect(SharedLink::query()->count())->toBe(0);
});

test('uma prancheta acessa os proprios links', function () {
    $board = Board::factory()->create();
    $link = SharedLink::factory()->for($board)->create();

    expect($board->sharedLinks->pluck('id')->all())->toBe([$link->id]);
});

test('o token e unico entre pranchetas diferentes', function () {
    SharedLink::factory()->create(['token' => 'token-repetido']);

    expect(fn () => SharedLink::factory()->create(['token' => 'token-repetido']))
        ->toThrow(QueryException::class);
});
