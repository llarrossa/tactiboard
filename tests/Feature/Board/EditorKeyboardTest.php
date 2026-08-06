<?php

use App\Enums\BoardVisibility;
use App\Models\Board;
use App\Models\SharedLink;
use Illuminate\Support\Str;

/**
 * Teclado no editor (Fase 5).
 *
 * Os atalhos vivem no Alpine e chamam os mesmos metodos que os botoes — o
 * comportamento do servidor ja esta coberto pelos testes de mover, remover,
 * duplicar e salvar. O que estes testes protegem e a outra metade: que a
 * pagina realmente entregue as ligacoes de teclado e que cada elemento do
 * campo seja alcancavel e anunciado corretamente.
 */
test('o editor escuta o teclado', function () {
    editorWith([ball('b1')])->assertSee('x-on:keydown.window="onShortcut($event)"', false);
});

test('o editor carrega o modulo de atalhos', function () {
    editorWith([])->assertSee('tactiboardEditorShortcuts()', false);
});

test('cada elemento do campo e alcancavel por teclado', function () {
    $html = editorWith([ball('b1'), arrow('a1')])->html();

    $group = Str::before(Str::after($html, 'canvas-element-b1'), '</g>');

    expect($group)->toContain('tabindex="0"')
        ->toContain('role="button"')
        // Enter e espaco selecionam; o foco sozinho nao seleciona, para nao
        // custar uma ida ao servidor por peca percorrida.
        ->toContain('keydown.enter')
        ->toContain('keydown.space');
});

test('o elemento anuncia o que e para quem nao ve o campo', function () {
    $html = editorWith([
        ['id' => 'p1', 'type' => 'player', 'team' => 'home', 'number' => 9, 'x' => 200, 'y' => 350],
        ['id' => 'p2', 'type' => 'player', 'team' => 'away', 'number' => 4, 'x' => 800, 'y' => 350],
        ['id' => 't1', 'type' => 'text', 'content' => 'Atacar profundidade', 'x' => 400, 'y' => 200],
        ball('b1'),
    ])->html();

    expect($html)->toContain('aria-label="Jogador 9 — Meu time"')
        ->toContain('aria-label="Jogador 4 — Adversário"')
        ->toContain('aria-label="Texto: Atacar profundidade"')
        ->toContain('aria-label="Bola"');
});

test('o elemento selecionado e anunciado como selecionado', function () {
    $html = editorWith([ball('b1'), ball('b2')])->call('select', 'b1')->html();

    $groupOf = fn (string $id): string => Str::before(Str::after($html, 'canvas-element-'.$id), '</g>');

    expect($groupOf('b1'))->toContain('aria-pressed="true"')
        ->and($groupOf('b2'))->toContain('aria-pressed="false"');
});

test('a pagina publica nao oferece teclado nem selecao no canvas', function () {
    // Fora do editor o canvas e desenho, nao controle: anunciar os elementos
    // como botao prometeria uma interacao que nao existe ali.
    $board = Board::factory()->create([
        'visibility' => BoardVisibility::Public,
        'canvas_data' => ['elements' => [ball('b1')]],
    ]);

    $link = SharedLink::factory()->for($board)->create();

    $this->get(route('share.show', $link->token))
        ->assertOk()
        ->assertDontSee('tabindex="0"', false)
        ->assertDontSee('role="button"', false)
        ->assertDontSee('onShortcut', false);
});

test('o editor mostra a legenda dos atalhos', function () {
    editorWith([])
        ->assertSee(__('Keyboard shortcuts'))
        ->assertSee(__('Move the selected element'))
        ->assertSee(__('Move in fine steps'));
});
