<?php

use App\Livewire\BoardEditor;
use App\Models\Board;
use App\Models\User;
use App\Rules\CanvasRules;
use Livewire\Livewire;

/**
 * Duplicar elemento (Fase 5).
 *
 * A copia nasce deslocada, com id proprio, e recebe a selecao — repetir o
 * atalho empilha a jogada em cascata.
 */
test('duplicar um elemento cria uma copia deslocada', function () {
    $editor = editorWith([ball('b1', 400, 300)])->call('duplicateElement', 'b1');

    $elements = $editor->get('elements');

    expect($elements)->toHaveCount(2)
        ->and($elements[1]['type'])->toBe('ball')
        // A copia nao pode nascer sobre o original, senao o usuario nao ve
        // nada acontecer.
        ->and([$elements[1]['x'], $elements[1]['y']])->not->toBe([400.0, 300.0]);
});

test('a copia recebe um id proprio', function () {
    $editor = editorWith([ball('b1')])->call('duplicateElement', 'b1');

    $ids = array_column($editor->get('elements'), 'id');

    expect($ids[1])->not->toBe('b1')
        ->and($ids[1])->not->toBeEmpty()
        ->and(array_unique($ids))->toHaveCount(2);
});

test('a copia fica selecionada', function () {
    $editor = editorWith([ball('b1')])->call('duplicateElement', 'b1');

    $copy = $editor->get('elements')[1];

    expect($editor->get('selectedId'))->toBe($copy['id']);
});

test('duplicar a copia empilha a jogada em cascata', function () {
    $editor = editorWith([ball('b1', 300, 300)]);

    $editor->call('duplicateElement', 'b1');
    $editor->call('duplicateElement', $editor->get('elements')[1]['id']);

    $positions = array_map(fn (array $e): array => [$e['x'], $e['y']], $editor->get('elements'));

    expect($positions)->toHaveCount(3)
        ->and(array_unique(array_map('json_encode', $positions)))->toHaveCount(3);
});

test('a copia de um jogador recebe o proximo numero livre do lado', function () {
    $editor = editorWith([
        ['id' => 'p1', 'type' => 'player', 'team' => 'home', 'number' => 9, 'x' => 200, 'y' => 350],
    ])->call('duplicateElement', 'p1');

    $elements = $editor->get('elements');

    // Dois jogadores do mesmo lado com o mesmo numero seriam a mesma peca
    // duas vezes no campo.
    expect($elements[1])->toMatchArray(['type' => 'player', 'team' => 'home', 'number' => 1]);
});

test('a copia de uma seta preserva o comprimento e a direcao', function () {
    $editor = editorWith([arrow('a1')])->call('duplicateElement', 'a1');

    $original = $editor->get('elements')[0];
    $copy = $editor->get('elements')[1];

    $length = fn (array $a): array => [
        $a['end']['x'] - $a['start']['x'],
        $a['end']['y'] - $a['start']['y'],
    ];

    // toEqual e nao toBe: o canvas gravado traz inteiros e o clamp devolve
    // float, entao o que importa aqui e o valor, nao o tipo.
    expect($length($copy))->toEqual($length($original))
        ->and($copy['start'])->not->toBe($original['start']);
});

test('a copia junto da borda desloca para dentro do campo', function () {
    $editor = editorWith([
        ball('b1', CanvasRules::FIELD_WIDTH, CanvasRules::FIELD_HEIGHT),
    ])->call('duplicateElement', 'b1');

    $copy = $editor->get('elements')[1];

    // Preso pelo limite do campo, o deslocamento normal deixaria a copia
    // exatamente sobre o original; ela vai para o outro lado.
    expect($copy['x'])->toBeLessThan(CanvasRules::FIELD_WIDTH)
        ->and($copy['y'])->toBeLessThan(CanvasRules::FIELD_HEIGHT)
        ->and($copy['x'])->toBeGreaterThanOrEqual(0)
        ->and($copy['y'])->toBeGreaterThanOrEqual(0);
});

test('a copia e aceita pelas regras do canvas', function () {
    $editor = editorWith([
        ['id' => 'p1', 'type' => 'player', 'team' => 'home', 'number' => 9, 'x' => 200, 'y' => 350],
        ball('b1'),
        ['id' => 't1', 'type' => 'text', 'content' => 'Atacar', 'x' => 400, 'y' => 200],
        arrow('a1'),
    ]);

    foreach (['p1', 'b1', 't1', 'a1'] as $id) {
        $editor->call('duplicateElement', $id);
    }

    $editor->call('save')->assertHasNoErrors();
});

test('duplicar um elemento inexistente nao muda o canvas', function () {
    $editor = editorWith([ball('b1')])->call('duplicateElement', 'nao-existe');

    expect($editor->get('elements'))->toHaveCount(1);
});

test('duplicar respeita o limite de elementos da prancheta', function () {
    $full = [];

    foreach (range(1, CanvasRules::MAX_ELEMENTS) as $i) {
        $full[] = ball("b{$i}", 100, 100);
    }

    editorWith($full)
        ->call('duplicateElement', 'b1')
        ->assertHasErrors('elements')
        ->assertCount('elements', CanvasRules::MAX_ELEMENTS);
});

test('um usuario nao duplica elementos na prancheta de outro', function () {
    $owner = User::factory()->create();
    $board = Board::factory()->for($owner)->create([
        'canvas_data' => ['elements' => [ball('b1')]],
    ]);

    $editor = Livewire::actingAs($owner)->test(BoardEditor::class, ['board' => $board]);

    auth()->login(User::factory()->create());

    $editor->call('duplicateElement', 'b1')->assertForbidden();
});

test('a autorizacao de duplicar nao depende do elemento existir', function () {
    // Sem a verificacao no topo, um id inexistente devolveria 200 a quem
    // perdeu o acesso, e a resposta passaria a depender do estado do canvas.
    $owner = User::factory()->create();
    $board = Board::factory()->for($owner)->create([
        'canvas_data' => ['elements' => [ball('b1')]],
    ]);

    $editor = Livewire::actingAs($owner)->test(BoardEditor::class, ['board' => $board]);

    auth()->login(User::factory()->create());

    $editor->call('duplicateElement', 'nao-existe')->assertForbidden();
});
