<?php

use App\Enums\BoardVisibility;
use App\Models\Board;
use App\Models\SharedLink;
use Illuminate\Support\Str;

/**
 * Responsividade do editor (Fase 5).
 *
 * O que da para proteger em teste sao as decisoes que viram atributo no HTML:
 * onde o gesto de toque e desligado e ate onde o campo pode crescer. O resto
 * e aparencia, e se verifica no navegador.
 */
test('o gesto de toque e desligado na peca, nao no campo inteiro', function () {
    // Desligar no campo impediria o usuario de rolar a pagina com o dedo
    // sobre a grama — no celular, a prancheta prenderia a tela.
    $html = editorWith([ball('b1')])->html();

    $field = Str::before(Str::after($html, '<svg'), '<defs');
    $piece = Str::before(Str::after($html, 'canvas-element-b1'), '</g>');

    expect($field)->not->toContain('touch-none')
        ->and($piece)->toContain('touch-action: none');
});

test('o campo tem altura maxima para nao empurrar os controles para fora da tela', function () {
    // A largura maxima acompanha a altura na proporcao do viewBox (1,5), para
    // que a limitacao nao deixe faixas vazias dos dois lados do gramado.
    editorWith([])
        ->assertSee('max-h-[70vh]', false)
        ->assertSee('max-w-[105vh]', false);
});

test('o campo da pagina publica tambem respeita a altura maxima', function () {
    $board = Board::factory()->create([
        'visibility' => BoardVisibility::Public,
        'canvas_data' => ['elements' => [ball('b1')]],
    ]);

    $link = SharedLink::factory()->for($board)->create();

    $this->get(route('share.show', $link->token))
        ->assertOk()
        ->assertSee('max-h-[70vh]', false)
        // A pagina publica continua sem desligar o toque: ali o campo e
        // imagem, e rolar com o dedo sobre ele deve rolar a pagina.
        ->assertDontSee('touch-action: none', false);
});

test('o painel de propriedades empilha no celular e alinha no desktop', function () {
    $html = editorWith([
        ['id' => 't1', 'type' => 'text', 'content' => 'Atacar', 'x' => 400, 'y' => 200],
    ])->call('select', 't1')->html();

    expect($html)->toContain('flex flex-col gap-4 sm:flex-row')
        ->toContain('sm:ms-auto');
});
