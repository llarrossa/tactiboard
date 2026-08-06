<?php

use App\Livewire\BoardEditor;
use App\Models\Board;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Limpar campo (Fase 5).
 *
 * A acao esvazia o editor mas nao grava: como toda edicao do canvas, ela so
 * vira canvas_data quando o usuario salva.
 */
test('limpar o campo remove todos os elementos', function () {
    $editor = editorWith([
        ball('b1'),
        ['id' => 'p1', 'type' => 'player', 'team' => 'home', 'number' => 9, 'x' => 200, 'y' => 350],
        arrow('a1'),
    ])->call('clearCanvas');

    expect($editor->get('elements'))->toBe([]);
});

test('limpar o campo desmarca o elemento selecionado', function () {
    $editor = editorWith([ball('b1')])
        ->call('select', 'b1')
        ->call('clearCanvas');

    expect($editor->get('selectedId'))->toBeNull();
});

test('limpar o campo nao grava sozinho', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create([
        'canvas_data' => ['elements' => [ball('b1')]],
    ]);

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        ->call('clearCanvas');

    // Recarregar a pagina sem salvar traz a jogada de volta — e o caminho de
    // arrependimento enquanto nao existe desfazer.
    expect($board->fresh()->canvas_data['elements'])->toHaveCount(1);
});

test('o campo limpo e gravado quando o usuario salva', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create([
        'canvas_data' => ['elements' => [ball('b1')]],
    ]);

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        ->call('clearCanvas')
        ->call('save')
        ->assertHasNoErrors();

    expect($board->fresh()->canvas_data['elements'])->toBe([]);
});

test('limpar o campo apaga a mensagem de erro do canvas', function () {
    $editor = editorWith([ball('b1')])
        ->set('elements', [['id' => 'quebrado', 'type' => 'foguete', 'x' => 10, 'y' => 10]])
        ->call('save')
        ->assertHasErrors();

    // O erro descrevia um elemento que acabou de sair do campo; manter a
    // mensagem sugeriria uma correcao que nao existe mais.
    $editor->call('clearCanvas')->assertHasNoErrors();
});

test('limpar um campo ja vazio nao quebra o editor', function () {
    $editor = editorWith([])->call('clearCanvas')->assertOk();

    expect($editor->get('elements'))->toBe([]);
});

test('um usuario nao limpa o campo da prancheta de outro', function () {
    $owner = User::factory()->create();
    $board = Board::factory()->for($owner)->create([
        'canvas_data' => ['elements' => [ball('b1')]],
    ]);

    $editor = Livewire::actingAs($owner)->test(BoardEditor::class, ['board' => $board]);

    auth()->login(User::factory()->create());

    $editor->call('clearCanvas')->assertForbidden();

    expect($board->fresh()->canvas_data['elements'])->toHaveCount(1);
});

test('o editor oferece limpar o campo e pede confirmacao', function () {
    $editor = editorWith([ball('b1')]);

    $editor->assertSee(__('Clear field'))
        ->assertSee('confirm-clear-canvas', false)
        ->assertSee(__('Clear the whole field?'));
});

test('o botao de limpar fica desabilitado com o campo vazio', function () {
    // O recorte olha so o botao, e a busca ignora a classe
    // `disabled:opacity-50`: sem os dois cuidados o teste passaria com
    // qualquer HTML, porque "disabled" tambem aparece no Alpine do modal.
    $clearButtonIsDisabled = function (array $elements): bool {
        $html = editorWith($elements)->html();
        $button = Str::before(Str::after($html, 'confirm-clear-canvas'), '</button>');

        return (bool) preg_match('/disabled(?!:)/', $button);
    };

    expect($clearButtonIsDisabled([]))->toBeTrue()
        ->and($clearButtonIsDisabled([ball('b1')]))->toBeFalse();
});
