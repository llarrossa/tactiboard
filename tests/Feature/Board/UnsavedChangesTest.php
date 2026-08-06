<?php

/**
 * Aviso de alteracoes nao salvas (Fase 5).
 *
 * O editor nao grava sozinho e nao tem desfazer: fechar a aba com uma jogada
 * por gravar perde o trabalho. A marca e posta por quem altera o canvas, nunca
 * por comparacao com o banco.
 */
test('uma prancheta recem-aberta nao tem alteracoes pendentes', function () {
    expect(editorWith([ball('b1')])->get('hasUnsavedChanges'))->toBeFalse();
});

test('adicionar um elemento marca alteracao pendente', function () {
    expect(editorWith([])->call('addBall')->get('hasUnsavedChanges'))->toBeTrue();
});

test('mover um elemento marca alteracao pendente', function () {
    expect(editorWith([ball('b1')])->call('moveElement', 'b1', 10, 10)->get('hasUnsavedChanges'))->toBeTrue();
});

test('remover um elemento marca alteracao pendente', function () {
    expect(editorWith([ball('b1')])->call('removeElement', 'b1')->get('hasUnsavedChanges'))->toBeTrue();
});

test('duplicar um elemento marca alteracao pendente', function () {
    expect(editorWith([ball('b1')])->call('duplicateElement', 'b1')->get('hasUnsavedChanges'))->toBeTrue();
});

test('limpar o campo marca alteracao pendente', function () {
    expect(editorWith([ball('b1')])->call('clearCanvas')->get('hasUnsavedChanges'))->toBeTrue();
});

test('limpar um campo ja vazio nao marca alteracao pendente', function () {
    // Nao mudou nada, entao nao ha o que avisar ao sair da pagina.
    expect(editorWith([])->call('clearCanvas')->get('hasUnsavedChanges'))->toBeFalse();
});

test('editar uma propriedade no painel marca alteracao pendente', function () {
    $editor = editorWith([
        ['id' => 'p1', 'type' => 'player', 'team' => 'home', 'number' => 9, 'x' => 200, 'y' => 350],
    ])->set('elements.0.number', 10);

    expect($editor->get('hasUnsavedChanges'))->toBeTrue();
});

test('selecionar um elemento nao marca alteracao pendente', function () {
    // Selecionar nao muda o canvas: avisar aqui ensinaria o usuario a ignorar
    // o aviso.
    $editor = editorWith([ball('b1')])
        ->call('select', 'b1')
        ->call('select', null);

    expect($editor->get('hasUnsavedChanges'))->toBeFalse();
});

test('salvar limpa a marca de alteracao pendente', function () {
    $editor = editorWith([])
        ->call('addBall')
        ->call('save')
        ->assertHasNoErrors();

    expect($editor->get('hasUnsavedChanges'))->toBeFalse();
});

test('um salvamento reprovado mantem a marca de alteracao pendente', function () {
    // O canvas continua por gravar: perder o aviso aqui esconderia justamente
    // o caso em que o trabalho corre risco.
    $editor = editorWith([ball('b1')])
        ->set('elements', [['id' => 'quebrado', 'type' => 'foguete', 'x' => 10, 'y' => 10]])
        ->call('save')
        ->assertHasErrors();

    expect($editor->get('hasUnsavedChanges'))->toBeTrue();
});

test('o editor mostra o aviso e prende a saida da pagina', function () {
    $editor = editorWith([]);

    $editor->assertDontSee(__('Unsaved changes.'));

    $editor->call('addBall')
        ->assertSee(__('Unsaved changes.'))
        ->assertSee('x-on:beforeunload.window', false)
        ->assertSee('$wire.hasUnsavedChanges', false);
});
