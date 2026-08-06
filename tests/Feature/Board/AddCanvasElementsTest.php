<?php

use App\Livewire\BoardEditor;
use App\Models\Board;
use App\Models\User;
use App\Rules\CanvasRules;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

function editorFor(?User $user = null): Testable
{
    $user ??= User::factory()->create();

    return Livewire::actingAs($user)->test(BoardEditor::class, [
        'board' => Board::factory()->for($user)->create(),
    ]);
}

test('cada tipo de elemento pode ser adicionado ao campo', function () {
    $editor = editorFor()
        ->call('addPlayer', 'home')
        ->call('addPlayer', 'away')
        ->call('addBall')
        ->call('addCone')
        ->call('addText')
        ->call('addArrow');

    expect(array_column($editor->get('elements'), 'type'))
        ->toBe(['player', 'player', 'ball', 'cone', 'text', 'arrow']);
});

test('os elementos adicionados sao aceitos pelas regras do canvas', function () {
    // Se o editor cria um elemento que a validacao recusa, o usuario monta a
    // jogada e nao consegue salvar. Este teste fecha essa porta.
    editorFor()
        ->call('addPlayer', 'home')
        ->call('addPlayer', 'away')
        ->call('addBall')
        ->call('addCone')
        ->call('addText')
        ->call('addArrow')
        ->call('save')
        ->assertHasNoErrors();
});

test('cada elemento nasce com um id proprio', function () {
    $editor = editorFor()
        ->call('addBall')
        ->call('addBall')
        ->call('addBall');

    $ids = array_column($editor->get('elements'), 'id');

    expect($ids)->toHaveCount(3)
        ->and(array_unique($ids))->toHaveCount(3)
        ->and($ids[0])->not->toBeEmpty();
});

test('os jogadores recebem o menor numero livre de cada lado', function () {
    $editor = editorFor()
        ->call('addPlayer', 'home')
        ->call('addPlayer', 'home')
        ->call('addPlayer', 'away');

    $players = $editor->get('elements');

    expect($players[0])->toMatchArray(['team' => 'home', 'number' => 1])
        ->and($players[1])->toMatchArray(['team' => 'home', 'number' => 2])
        // A numeracao do adversario e independente da do proprio time.
        ->and($players[2])->toMatchArray(['team' => 'away', 'number' => 1]);
});

test('o numero livre considera os buracos deixados por remocoes', function () {
    $editor = editorFor()
        ->call('addPlayer', 'home')
        ->call('addPlayer', 'home')
        ->call('addPlayer', 'home');

    $elements = $editor->get('elements');
    unset($elements[1]);

    $editor->set('elements', array_values($elements))->call('addPlayer', 'home');

    expect(array_column($editor->get('elements'), 'number'))->toBe([1, 3, 2]);
});

test('os elementos novos nascem dentro do campo', function () {
    $editor = editorFor();

    foreach (range(1, 12) as $ignored) {
        $editor->call('addBall');
    }

    foreach ($editor->get('elements') as $element) {
        expect($element['x'])->toBeGreaterThanOrEqual(0)
            ->toBeLessThanOrEqual(CanvasRules::FIELD_WIDTH)
            ->and($element['y'])->toBeGreaterThanOrEqual(0)
            ->toBeLessThanOrEqual(CanvasRules::FIELD_HEIGHT);
    }
});

test('a seta nasce com os dois pontos e sem coordenada solta', function () {
    $arrow = editorFor()->call('addArrow')->get('elements')[0];

    expect($arrow)->toHaveKeys(['start', 'end'])
        ->and($arrow)->not->toHaveKey('x')
        ->and($arrow['start'])->toHaveKeys(['x', 'y']);
});

test('um lado de jogador invalido e recusado', function () {
    editorFor()->call('addPlayer', 'arbitro')->assertStatus(400);
});

test('o editor recusa passar do limite de elementos', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create();

    $full = [];

    foreach (range(1, CanvasRules::MAX_ELEMENTS) as $i) {
        $full[] = ['id' => "b{$i}", 'type' => 'ball', 'x' => 100, 'y' => 100];
    }

    Livewire::actingAs($user)
        ->test(BoardEditor::class, ['board' => $board])
        ->set('elements', $full)
        ->call('addBall')
        ->assertHasErrors('elements')
        ->assertCount('elements', CanvasRules::MAX_ELEMENTS);
});

test('com os 99 numeros do lado em campo o jogador novo repete o ultimo', function () {
    // Recusar o elemento seria pior: o usuario ajusta o numero no painel, e um
    // campo com 99 jogadores do mesmo lado ja saiu do uso real ha muito.
    $lotado = array_map(fn (int $numero) => [
        'id' => 'p'.$numero,
        'type' => 'player',
        'team' => 'home',
        'number' => $numero,
        'x' => 100,
        'y' => 100,
    ], range(1, 99));

    $editor = editorWith($lotado)->call('addPlayer', 'home');

    expect($editor->get('elements'))->toHaveCount(100)
        ->and($editor->get('elements')[99]['number'])->toBe(99);
});

test('um usuario nao adiciona elementos na prancheta de outro', function () {
    $owner = User::factory()->create();
    $board = Board::factory()->for($owner)->create();

    $editor = Livewire::actingAs($owner)->test(BoardEditor::class, ['board' => $board]);

    auth()->login(User::factory()->create());

    $editor->call('addBall')->assertForbidden();
});

test('um elemento malformado nao derruba o editor e nao e desenhado', function () {
    $editor = editorFor()->call('addBall');

    $valido = $editor->get('elements')[0];

    // Um payload adulterado nao pode virar erro de servidor: o editor continua
    // de pe, desenha o que da e a mensagem de validacao explica o resto.
    $editor->set('elements', [
        $valido,
        ['id' => 'quebrado', 'type' => 'foguete', 'x' => 10, 'y' => 10],
        ['id' => 'sem-numero', 'type' => 'player', 'team' => 'home', 'x' => 10, 'y' => 10],
    ])->assertOk();

    $editor->assertSee('canvas-element-'.$valido['id'], false)
        ->assertDontSee('canvas-element-quebrado', false)
        ->assertDontSee('canvas-element-sem-numero', false);

    $editor->call('save')->assertHasErrors();
});

test('os elementos do canvas sao desenhados no campo', function () {
    $user = User::factory()->create();
    $board = Board::factory()->for($user)->create([
        'canvas_data' => ['elements' => [
            ['id' => 'p1', 'type' => 'player', 'team' => 'home', 'number' => 9, 'x' => 200, 'y' => 350],
            ['id' => 'p2', 'type' => 'player', 'team' => 'away', 'number' => 4, 'x' => 800, 'y' => 350],
            ['id' => 'b1', 'type' => 'ball', 'x' => 525, 'y' => 340],
            ['id' => 'c1', 'type' => 'cone', 'x' => 300, 'y' => 200],
            ['id' => 't1', 'type' => 'text', 'content' => 'Atacar profundidade', 'x' => 400, 'y' => 200],
            ['id' => 'a1', 'type' => 'arrow', 'start' => ['x' => 200, 'y' => 350], 'end' => ['x' => 300, 'y' => 250]],
        ]],
    ]);

    $editor = Livewire::actingAs($user)->test(BoardEditor::class, ['board' => $board]);

    $editor->assertSee('canvas-element-p1', false)
        ->assertSee('canvas-element-a1', false)
        // Numero do jogador e conteudo do texto aparecem desenhados.
        ->assertSee('>9</text>', false)
        ->assertSee('Atacar profundidade')
        // A seta usa os dois pontos e a ponta compartilhada.
        ->assertSee('marker-end="url(#tactiboard-arrowhead)"', false);
});
