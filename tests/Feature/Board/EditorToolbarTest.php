<?php

use App\Rules\CanvasRules;
use Illuminate\Support\Str;

/**
 * Toolbar do editor (Fase 5).
 *
 * A barra agrupa o que adiciona e o que age sobre o campo, e mostra quanto
 * espaco resta na prancheta antes de o limite ser atingido.
 */
function fullCanvas(): array
{
    $elements = [];

    foreach (range(1, CanvasRules::MAX_ELEMENTS) as $i) {
        $elements[] = ball("b{$i}", 100, 100);
    }

    return $elements;
}

test('a toolbar mostra quantos elementos a prancheta tem', function () {
    editorWith([ball('b1'), ball('b2'), ball('b3')])
        ->assertSee('3 de '.CanvasRules::MAX_ELEMENTS.' elementos');
});

test('o contador acompanha o que o usuario adiciona', function () {
    editorWith([])
        ->assertSee('0 de '.CanvasRules::MAX_ELEMENTS.' elementos')
        ->call('addBall')
        ->assertSee('1 de '.CanvasRules::MAX_ELEMENTS.' elementos');
});

test('a toolbar separa adicionar de agir sobre o campo', function () {
    editorWith([])
        ->assertSee(__('Add elements'))
        ->assertSee(__('Field'))
        ->assertSee('aria-labelledby="toolbar-add-label"', false)
        ->assertSee('aria-labelledby="toolbar-field-label"', false);
});

test('os botoes de adicionar sao desabilitados quando a prancheta enche', function () {
    // Descobrir o limite por mensagem de erro, com a jogada ja montada, e
    // tarde demais: o botao para de convidar antes disso.
    $addButton = function (array $elements): string {
        $html = editorWith($elements)->html();

        return Str::before(Str::after($html, 'wire:click="addBall"'), '</button>');
    };

    expect($addButton(fullCanvas()))->toMatch('/disabled(?!:)/')
        ->and($addButton([ball('b1')]))->not->toMatch('/disabled(?!:)/');
});

test('a prancheta cheia destaca o contador', function () {
    editorWith(fullCanvas())
        ->assertSee(CanvasRules::MAX_ELEMENTS.' de '.CanvasRules::MAX_ELEMENTS.' elementos')
        ->assertSee('text-red-600', false);
});

test('a toolbar nao repete duplicar e remover', function () {
    // As duas acoes pertencem ao elemento selecionado e vivem no painel de
    // propriedades; repeti-las na barra daria dois caminhos para a mesma coisa.
    $html = editorWith([ball('b1')])->html();

    $toolbar = Str::before($html, 'toolbar-field-label');

    expect($toolbar)->not->toContain('duplicateElement')
        ->and($toolbar)->not->toContain('removeElement');
});
