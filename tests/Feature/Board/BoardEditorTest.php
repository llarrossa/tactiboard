<?php

use App\Livewire\BoardEditor;
use App\Models\Board;
use App\Models\User;
use Livewire\Livewire;

/*
 * Quem pode alcancar esta tela e assunto da rota, coberto por ShowBoardTest:
 * o editor vive em boards.show, que ja passa pela BoardPolicy via `can:view`.
 * Aqui o foco e o editor em si.
 */

test('o dono abre o editor na tela da prancheta', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('boards.show', $board))
        ->assertOk()
        ->assertSeeLivewire(BoardEditor::class);
});

test('o editor desenha o campo no sistema de coordenadas do canvas', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        ->assertOk()
        // O gramado vai de (0,0) a (1050,680): e nesse espaco que as
        // coordenadas dos elementos sao persistidas.
        ->assertSee('<rect x="0" y="0" width="1050" height="680"', false)
        ->assertSee('viewBox="-30 -30 1110 740"', false);
});

test('o campo traz as marcacoes oficiais exigidas pelo RF-009', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        // Meio-campo, circulo central, grandes areas e pequenas areas.
        ->assertSee('<line x1="525" y1="0" x2="525" y2="680"', false)
        ->assertSee('<circle cx="525" cy="340" r="91.5"', false)
        ->assertSee('<rect x="0" y="138.4" width="165" height="403.2"', false)
        ->assertSee('<rect x="885" y="138.4" width="165" height="403.2"', false)
        ->assertSee('<rect x="0" y="248.4" width="55" height="183.2"', false)
        ->assertSee('<rect x="995" y="248.4" width="55" height="183.2"', false);
});
