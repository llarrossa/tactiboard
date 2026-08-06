<?php

use App\Enums\CanvasElementType;

test('os valores persistidos sao os cinco tipos de docs/03', function () {
    // Jogador do time e adversario compartilham o tipo `player` e se
    // distinguem por `team`: sao a mesma peca em cores diferentes.
    expect(CanvasElementType::values())
        ->toBe(['player', 'ball', 'cone', 'text', 'arrow']);
});

test('cada tipo tem um rotulo', function () {
    foreach (CanvasElementType::cases() as $type) {
        expect($type->label())->toBeString()->not->toBeEmpty();
    }
});

test('os rotulos nao se repetem', function () {
    $labels = array_map(fn (CanvasElementType $t) => $t->label(), CanvasElementType::cases());

    expect(array_unique($labels))->toHaveCount(count($labels));
});

test('a seta e o unico tipo nao posicional', function () {
    // Quem decide se o elemento guarda x/y ou start/end e este metodo, tanto
    // no desenho quanto na normalizacao antes de gravar.
    expect(CanvasElementType::Arrow->isPositional())->toBeFalse();

    foreach (CanvasElementType::cases() as $type) {
        if ($type !== CanvasElementType::Arrow) {
            expect($type->isPositional())->toBeTrue();
        }
    }
});
