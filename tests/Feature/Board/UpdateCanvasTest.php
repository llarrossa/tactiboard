<?php

use App\Livewire\BoardEditor;
use App\Models\Board;
use App\Models\User;
use Livewire\Livewire;

/**
 * @return array<string, mixed>
 */
function player(string $id, int $number = 9, float $x = 200, float $y = 350): array
{
    return ['id' => $id, 'type' => 'player', 'team' => 'home', 'number' => $number, 'x' => $x, 'y' => $y];
}

test('o dono salva o canvas e o recupera ao reabrir a prancheta', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    $elements = [
        player('p1'),
        ['id' => 'b1', 'type' => 'ball', 'x' => 525, 'y' => 340],
        ['id' => 'c1', 'type' => 'cone', 'x' => 300, 'y' => 200],
        ['id' => 't1', 'type' => 'text', 'content' => 'Atacar profundidade', 'x' => 400, 'y' => 200],
        ['id' => 'a1', 'type' => 'arrow', 'start' => ['x' => 200, 'y' => 350], 'end' => ['x' => 300, 'y' => 250]],
    ];

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        ->set('elements', $elements)
        ->call('save')
        ->assertHasNoErrors();

    // O MySQL nao preserva a ordem das chaves em coluna json (docs/03 secao
    // 6.1), entao a comparacao e por conteudo.
    expect($board->refresh()->canvas_data['elements'])->toEqual($elements);

    // Reabrir a prancheta devolve o mesmo estado ao editor.
    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board->fresh()])
        ->assertSet('elements', $elements);
});

test('o canvas de uma prancheta nova comeca vazio', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => Board::factory()->for($user)->create()])
        ->assertSet('elements', []);
});

test('as chaves que nao pertencem ao tipo sao descartadas ao salvar', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        ->set('elements', [
            // Um cone nao tem numero nem time; se o elemento ja foi jogador,
            // essas chaves nao podem sobreviver no JSON gravado.
            ['id' => 'c1', 'type' => 'cone', 'x' => 300, 'y' => 200, 'number' => 9, 'team' => 'home'],
        ])
        ->call('save')
        ->assertHasNoErrors();

    expect($board->refresh()->canvas_data['elements'][0])
        ->toEqual(['id' => 'c1', 'type' => 'cone', 'x' => 300.0, 'y' => 200.0]);
});

test('remover um elemento do meio da lista grava um array e nao um objeto', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    $component = Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        ->set('elements', [player('p1'), player('p2', 10), player('p3', 11)]);

    // Remover pelo indice deixa buracos nas chaves do array PHP; sem
    // reindexar, o JSON viraria um objeto e o editor nao leria de volta.
    $withHole = $component->get('elements');
    unset($withHole[1]);

    $component->set('elements', $withHole)->call('save')->assertHasNoErrors();

    $saved = $board->refresh()->canvas_data['elements'];

    expect(array_keys($saved))->toBe([0, 1])
        ->and(array_column($saved, 'id'))->toBe(['p1', 'p3']);
});

test('um elemento fora do campo e recusado', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        ->set('elements', [player('p1', 9, 1051, 350)])
        ->call('save')
        ->assertHasErrors('elements.0.x');

    expect($board->refresh()->canvas_data['elements'])->toBe([]);
});

test('um tipo de elemento inexistente e recusado', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        ->set('elements', [['id' => 'x1', 'type' => 'foguete', 'x' => 100, 'y' => 100]])
        ->call('save')
        ->assertHasErrors('elements.0.type');
});

test('dois elementos nao podem repetir o mesmo id', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        ->set('elements', [player('mesmo-id'), player('mesmo-id', 10)])
        ->call('save')
        ->assertHasErrors('elements.0.id');
});

test('um jogador exige numero e time validos', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        ->set('elements', [['id' => 'p1', 'type' => 'player', 'x' => 200, 'y' => 350]])
        ->call('save')
        ->assertHasErrors(['elements.0.team', 'elements.0.number']);

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        ->set('elements', [player('p1', 120)])
        ->call('save')
        ->assertHasErrors('elements.0.number');
});

test('um texto respeita o limite de tamanho', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        ->set('elements', [
            ['id' => 't1', 'type' => 'text', 'content' => str_repeat('a', 121), 'x' => 400, 'y' => 200],
        ])
        ->call('save')
        ->assertHasErrors('elements.0.content');
});

test('uma chave sobrando de outro tipo nao impede o salvamento', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        ->set('elements', [
            // Sobra de quando o elemento era jogador, com valor invalido. Como
            // a chave nao pertence ao cone, ela e descartada e nao reprovada.
            ['id' => 'c1', 'type' => 'cone', 'x' => 300, 'y' => 200, 'team' => ['home']],
        ])
        ->call('save')
        ->assertHasNoErrors();

    expect($board->refresh()->canvas_data['elements'][0])
        ->toEqual(['id' => 'c1', 'type' => 'cone', 'x' => 300.0, 'y' => 200.0]);
});

test('uma seta sem os dois pontos e recusada', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        ->set('elements', [['id' => 'a1', 'type' => 'arrow']])
        ->call('save')
        ->assertHasErrors(['elements.0.start', 'elements.0.end']);
});

test('um usuario nao abre o editor da prancheta de outro', function () {
    // O componente e uma fronteira propria: nao depende da autorizacao da rota
    // que o renderiza.
    Livewire::actingAs(User::factory()->create())
        ->test(BoardEditor::class, ['board' => Board::factory()->create()])
        ->assertForbidden();
});

test('o salvamento reverifica a permissao com o editor ja aberto', function () {
    $owner = User::factory()->create();
    $board = Board::factory()->for($owner)->create();

    $editor = Livewire::actingAs($owner)
        ->test(BoardEditor::class, ['board' => $board])
        ->set('elements', [player('p1')]);

    // A sessao muda depois da montagem: o save() nao pode se apoiar na
    // autorizacao feita quando o editor foi aberto.
    auth()->login(User::factory()->create());

    $editor->call('save')->assertForbidden();

    expect($board->refresh()->canvas_data['elements'])->toBe([]);
});

test('salvar o canvas nao altera o dono nem os dados descritivos', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create(['title' => 'Saida de bola 4-3-3']);

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        ->set('elements', [player('p1')])
        ->call('save')
        ->assertHasNoErrors();

    expect($board->refresh())
        ->user_id->toBe($user->id)
        ->title->toBe('Saida de bola 4-3-3');
});
