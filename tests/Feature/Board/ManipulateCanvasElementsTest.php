<?php

use App\Livewire\BoardEditor;
use App\Models\Board;
use App\Models\User;
use App\Rules\CanvasRules;
use Livewire\Livewire;

test('arrastar um elemento muda a posicao dele', function () {
    $editor = editorWith([ball()])->call('moveElement', 'b1', 40, -25);

    expect($editor->get('elements')[0])->toMatchArray(['x' => 540.0, 'y' => 275.0]);
});

test('um elemento arrastado para fora para na borda do campo', function () {
    $editor = editorWith([ball('b1', 20, 20)])->call('moveElement', 'b1', -500, -500);

    expect($editor->get('elements')[0])->toMatchArray(['x' => 0.0, 'y' => 0.0]);

    $editor->call('moveElement', 'b1', 99999, 99999);

    expect($editor->get('elements')[0])->toMatchArray([
        'x' => (float) CanvasRules::FIELD_WIDTH,
        'y' => (float) CanvasRules::FIELD_HEIGHT,
    ]);
});

test('arrastar uma seta move as duas pontas juntas', function () {
    $editor = editorWith([arrow()])->call('moveElement', 'a1', 50, 30);

    expect($editor->get('elements')[0]['start'])->toEqual(['x' => 150.0, 'y' => 130.0])
        ->and($editor->get('elements')[0]['end'])->toEqual(['x' => 250.0, 'y' => 230.0]);
});

test('arrastar uma ponta move so aquela ponta', function () {
    $editor = editorWith([arrow()])->call('moveElement', 'a1', 50, 30, 'end');

    expect($editor->get('elements')[0]['start'])->toEqual(['x' => 100.0, 'y' => 100.0])
        ->and($editor->get('elements')[0]['end'])->toEqual(['x' => 250.0, 'y' => 230.0]);
});

test('uma ponta invalida move a seta inteira em vez de corromper o elemento', function () {
    // `part` chega do navegador; um valor fora de start/end nao pode gravar
    // uma chave nova no elemento.
    $editor = editorWith([arrow()])->call('moveElement', 'a1', 10, 10, 'meio');

    expect(array_keys($editor->get('elements')[0]))->toBe(['id', 'type', 'start', 'end'])
        ->and($editor->get('elements')[0]['start'])->toEqual(['x' => 110.0, 'y' => 110.0]);
});

test('mover a seta inteira contra a borda preserva o comprimento dela', function () {
    // Prender cada ponta em separado encolheria a seta ao encostar na linha,
    // mudando a jogada que o usuario desenhou.
    $editor = editorWith([arrow()])->call('moveElement', 'a1', -150, 0);

    $movida = $editor->get('elements')[0];

    expect($movida['start'])->toEqual(['x' => 0.0, 'y' => 100.0])
        ->and($movida['end'])->toEqual(['x' => 100.0, 'y' => 200.0]);

    // Mesma coisa na borda oposta.
    $editor->call('moveElement', 'a1', 99999, 99999);

    $movida = $editor->get('elements')[0];

    expect($movida['end']['x'] - $movida['start']['x'])->toBe(100.0)
        ->and($movida['end']['y'] - $movida['start']['y'])->toBe(100.0)
        ->and($movida['end']['x'])->toBe((float) CanvasRules::FIELD_WIDTH)
        ->and($movida['end']['y'])->toBe((float) CanvasRules::FIELD_HEIGHT);
});

test('arrastar uma ponta sozinha continua podendo mudar o comprimento', function () {
    $editor = editorWith([arrow()])->call('moveElement', 'a1', -500, 0, 'start');

    expect($editor->get('elements')[0]['start'])->toEqual(['x' => 0.0, 'y' => 100.0])
        ->and($editor->get('elements')[0]['end'])->toEqual(['x' => 200.0, 'y' => 200.0]);
});

test('um elemento adulterado que nao e array nao derruba o editor', function () {
    $editor = editorWith([ball('b1'), ball('b2')]);

    $editor->set('elements', [ball('b1'), 'quebrado'])
        ->call('moveElement', 'b1', 10, 10)
        ->assertOk();

    expect($editor->get('elements')[0])->toMatchArray(['x' => 510.0, 'y' => 310.0]);
});

test('o painel some quando o elemento selecionado fica malformado', function () {
    $editor = editorWith([
        ['id' => 'p1', 'type' => 'player', 'team' => 'home', 'number' => 9, 'x' => 200, 'y' => 350],
    ])->call('select', 'p1')->assertSee(__('Selected element'));

    // O painel le o elemento cru para escrever nele; se ele estiver quebrado,
    // abrir o painel quebraria o render inteiro.
    $editor->set('elements.0.type', 'foguete')
        ->assertOk()
        ->assertDontSee(__('Selected element'));
});

test('mover um elemento que ja nao existe nao quebra o editor', function () {
    $editor = editorWith([ball()])->call('moveElement', 'fantasma', 10, 10)->assertOk();

    expect($editor->get('elements'))->toHaveCount(1);
});

test('remover um elemento que ja nao existe nao muda o canvas', function () {
    // O id chega do navegador e pode se referir a uma peca que outra aba ja
    // removeu. Sair sem alterar nada e o comportamento certo.
    $editor = editorWith([ball('b1')])->call('removeElement', 'fantasma')->assertOk();

    expect(array_column($editor->get('elements'), 'id'))->toBe(['b1']);
});

test('clicar em um elemento seleciona ele', function () {
    $editor = editorWith([ball(), ball('b2')])->call('select', 'b2');

    expect($editor->get('selectedId'))->toBe('b2');

    $editor->call('select', null);

    expect($editor->get('selectedId'))->toBeNull();
});

test('selecionar um elemento inexistente nao seleciona nada', function () {
    expect(editorWith([ball()])->call('select', 'fantasma')->get('selectedId'))->toBeNull();
});

test('o elemento selecionado ganha o painel de propriedades', function () {
    $editor = editorWith([
        ['id' => 'p1', 'type' => 'player', 'team' => 'home', 'number' => 9, 'x' => 200, 'y' => 350],
    ]);

    $editor->assertDontSee(__('Selected element'))
        ->call('select', 'p1')
        ->assertSee(__('Selected element'))
        ->assertSee(__('Number'));
});

test('remover um elemento tira ele do campo e preserva os demais', function () {
    $editor = editorWith([ball('b1'), ball('b2'), ball('b3')])->call('removeElement', 'b2');

    expect(array_column($editor->get('elements'), 'id'))->toBe(['b1', 'b3']);

    $editor->call('save')->assertHasNoErrors();

    expect(array_column($editor->get('elements'), 'id'))->toBe(['b1', 'b3']);
});

test('remover o elemento selecionado limpa a selecao', function () {
    $editor = editorWith([ball()])->call('select', 'b1')->call('removeElement', 'b1');

    expect($editor->get('selectedId'))->toBeNull()
        ->and($editor->get('elements'))->toBe([]);
});

test('o numero do jogador pode ser alterado pelo painel', function () {
    $editor = editorWith([
        ['id' => 'p1', 'type' => 'player', 'team' => 'home', 'number' => 9, 'x' => 200, 'y' => 350],
    ]);

    $editor->call('select', 'p1')
        ->set('elements.0.number', 10)
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasNoErrors();

    expect($editor->get('elements')[0]['number'])->toBe(10);
});

test('um numero de jogador invalido avisa na hora', function () {
    $editor = editorWith([
        ['id' => 'p1', 'type' => 'player', 'team' => 'home', 'number' => 9, 'x' => 200, 'y' => 350],
    ]);

    $editor->call('select', 'p1')
        ->set('elements.0.number', 500)
        ->assertHasErrors('elements.0.number');
});

test('o conteudo do texto pode ser alterado pelo painel', function () {
    $editor = editorWith([
        ['id' => 't1', 'type' => 'text', 'content' => 'Nota', 'x' => 400, 'y' => 200],
    ]);

    $editor->call('select', 't1')
        ->set('elements.0.content', 'Cobertura defensiva')
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasNoErrors();

    expect($editor->get('elements')[0]['content'])->toBe('Cobertura defensiva');
});

test('um usuario nao move nem remove elementos da prancheta de outro', function () {
    $owner = User::factory()->create();
    $board = Board::factory()->for($owner)->create(['canvas_data' => ['elements' => [ball()]]]);

    $editor = Livewire::actingAs($owner)->test(BoardEditor::class, ['board' => $board]);

    auth()->login(User::factory()->create());

    $editor->call('moveElement', 'b1', 10, 10)->assertForbidden();

    $editor = Livewire::actingAs($owner)->test(BoardEditor::class, ['board' => $board]);

    auth()->login(User::factory()->create());

    $editor->call('removeElement', 'b1')->assertForbidden();

    expect($board->refresh()->canvas_data['elements'])->toHaveCount(1);
});

test('a jogada montada sobrevive a fechar e reabrir a prancheta', function () {
    // O criterio de conclusao da Fase 3: montar, salvar, fechar e reabrir
    // mantendo a configuracao.
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        ->call('addPlayer', 'home')
        ->call('addPlayer', 'away')
        ->call('addBall')
        ->call('addArrow')
        ->call('save')
        ->assertHasNoErrors();

    $saved = $board->refresh()->canvas_data['elements'];

    $reopened = Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => Board::find($board->id)]);

    expect($reopened->get('elements'))->toEqual($saved)
        ->and($saved)->toHaveCount(4);
});
