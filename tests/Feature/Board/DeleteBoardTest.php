<?php

use App\Models\Board;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('o dono exclui a propria prancheta', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    $response = $this->actingAs($user)->delete(route('boards.destroy', $board));

    $response->assertRedirect(route('dashboard'));
    expect($board->fresh())->toBeNull();
});

test('a exclusao e definitiva, sem soft delete', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    $this->actingAs($user)->delete(route('boards.destroy', $board));

    // docs/03 §10: o MVP nao usa Soft Delete, o registro sai da tabela.
    expect(Board::count())->toBe(0)
        ->and(DB::table('boards')->where('id', $board->id)->exists())->toBeFalse();
});

test('a prancheta excluida deixa de ser acessivel', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();
    $rota = route('boards.show', $board);

    $this->actingAs($user)->delete(route('boards.destroy', $board));

    $this->actingAs($user)->get($rota)->assertNotFound();
});

test('um usuario nao exclui a prancheta de outro', function () {
    $board = Board::factory()->create();

    $response = $this->actingAs(User::factory()->create())
        ->delete(route('boards.destroy', $board));

    $response->assertForbidden();
    expect($board->fresh())->not->toBeNull();
});

test('um visitante nao exclui prancheta', function () {
    $board = Board::factory()->create();

    $this->delete(route('boards.destroy', $board))->assertRedirect(route('login'));

    expect($board->fresh())->not->toBeNull();
});

test('excluir a conta remove as pranchetas do usuario', function () {
    $user = User::factory()->create();
    Board::factory()->for($user)->count(3)->create();
    Board::factory()->create();

    $this->actingAs($user)->delete('/profile', ['password' => 'password']);

    // A FK de boards.user_id tem ON DELETE CASCADE: sem isso o banco recusaria
    // a exclusao da conta ou deixaria prancheta orfa.
    expect(Board::count())->toBe(1);
});
