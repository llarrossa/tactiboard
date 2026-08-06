<?php

use App\Rules\CanvasRules;
use Illuminate\Support\Facades\Validator;

/**
 * Roda o validator de verdade sobre um canvas, que e como CanvasRules e usada
 * pelo BoardEditor. Conferir apenas o conteudo do array de regras nao pegaria
 * erro na expansao dos curingas nem na semantica de exclude_if/exclude_unless.
 *
 * @param  array<int, array<string, mixed>>  $elements
 * @return array<int, string>
 */
function canvasErrors(array $elements): array
{
    return Validator::make(
        ['elements' => $elements],
        CanvasRules::rules(),
        CanvasRules::messages()
    )->errors()->keys();
}

test('um canvas com um elemento valido de cada tipo passa', function () {
    expect(canvasErrors([
        ['id' => 'p1', 'type' => 'player', 'team' => 'home', 'number' => 9, 'x' => 200, 'y' => 350],
        ['id' => 'p2', 'type' => 'player', 'team' => 'away', 'number' => 4, 'x' => 800, 'y' => 350],
        ['id' => 'b1', 'type' => 'ball', 'x' => 525, 'y' => 340],
        ['id' => 'c1', 'type' => 'cone', 'x' => 300, 'y' => 200],
        ['id' => 't1', 'type' => 'text', 'content' => 'Atacar profundidade', 'x' => 400, 'y' => 200],
        ['id' => 'a1', 'type' => 'arrow', 'start' => ['x' => 0, 'y' => 0], 'end' => ['x' => 1050, 'y' => 680]],
    ]))->toBe([]);
});

test('uma chave que nao pertence ao tipo nao reprova o canvas, mesmo invalida', function () {
    // Sobra de quando o elemento era outro tipo. Como normalize() vai descartar
    // essas chaves, a validacao nao pode reprovar o salvamento por causa delas.
    expect(canvasErrors([
        ['id' => 'c1', 'type' => 'cone', 'x' => 300, 'y' => 200, 'team' => ['home'], 'number' => 'nove'],
        ['id' => 'b1', 'type' => 'ball', 'x' => 525, 'y' => 340, 'start' => 'invalido'],
        ['id' => 'a1', 'type' => 'arrow', 'start' => ['x' => 1, 'y' => 2], 'end' => ['x' => 3, 'y' => 4], 'x' => 'longe'],
    ]))->toBe([]);
});

test('um elemento posicional sem coordenada e recusado', function () {
    expect(canvasErrors([['id' => 'b1', 'type' => 'ball']]))
        ->toBe(['elements.0.x', 'elements.0.y']);
});

test('uma seta sem os dois pontos e recusada', function () {
    expect(canvasErrors([['id' => 'a1', 'type' => 'arrow']]))
        ->toContain('elements.0.start')
        ->toContain('elements.0.end');
});

test('uma seta com ponta fora do campo e recusada', function () {
    expect(canvasErrors([
        ['id' => 'a1', 'type' => 'arrow', 'start' => ['x' => 0, 'y' => 0], 'end' => ['x' => 1051, 'y' => 0]],
    ]))->toBe(['elements.0.end.x']);
});

test('o canvas recusa mais elementos do que o limite', function () {
    $elements = [];

    foreach (range(1, CanvasRules::MAX_ELEMENTS + 1) as $i) {
        $elements[] = ['id' => "b{$i}", 'type' => 'ball', 'x' => 1, 'y' => 1];
    }

    expect(canvasErrors($elements))->toContain('elements');
});

test('a normalizacao mantem apenas as chaves de cada tipo', function () {
    $normalized = CanvasRules::normalize([
        ['id' => 'p1', 'type' => 'player', 'team' => 'away', 'number' => '7', 'x' => 200, 'y' => 350, 'lixo' => 1],
        ['id' => 'b1', 'type' => 'ball', 'x' => 525, 'y' => 340, 'number' => 9],
        ['id' => 't1', 'type' => 'text', 'content' => 'Cobertura', 'x' => 400, 'y' => 200, 'team' => 'home'],
        ['id' => 'a1', 'type' => 'arrow', 'x' => 1, 'y' => 2, 'start' => ['x' => 10, 'y' => 20], 'end' => ['x' => 30, 'y' => 40]],
    ]);

    expect($normalized[0])->toBe(['id' => 'p1', 'type' => 'player', 'x' => 200.0, 'y' => 350.0, 'team' => 'away', 'number' => 7])
        ->and($normalized[1])->toBe(['id' => 'b1', 'type' => 'ball', 'x' => 525.0, 'y' => 340.0])
        ->and($normalized[2])->toBe(['id' => 't1', 'type' => 'text', 'x' => 400.0, 'y' => 200.0, 'content' => 'Cobertura'])
        // A seta nao e posicional: ela e definida por start e end, nao por x/y.
        ->and($normalized[3])->toBe([
            'id' => 'a1',
            'type' => 'arrow',
            'start' => ['x' => 10.0, 'y' => 20.0],
            'end' => ['x' => 30.0, 'y' => 40.0],
        ]);
});

test('a normalizacao arredonda as coordenadas para uma casa decimal', function () {
    $normalized = CanvasRules::normalize([
        ['id' => 'b1', 'type' => 'ball', 'x' => 524.98765, 'y' => 340.44444],
    ]);

    expect($normalized[0]['x'])->toBe(525.0)
        ->and($normalized[0]['y'])->toBe(340.4);
});

test('a normalizacao reindexa a lista', function () {
    $withHole = [0 => ['id' => 'b1', 'type' => 'ball', 'x' => 1, 'y' => 2]];
    $withHole[5] = ['id' => 'c1', 'type' => 'cone', 'x' => 3, 'y' => 4];

    expect(array_keys(CanvasRules::normalize($withHole)))->toBe([0, 1]);
});
