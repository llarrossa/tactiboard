<?php

use App\Enums\BoardCategory;

test('os valores persistidos sao os definidos em docs/03', function () {
    expect(BoardCategory::values())
        ->toBe(['attack', 'defense', 'set_piece', 'training', 'other']);
});

test('cada categoria tem um rotulo', function () {
    foreach (BoardCategory::cases() as $category) {
        expect($category->label())->toBeString()->not->toBeEmpty();
    }
});

test('os rotulos nao se repetem', function () {
    $labels = array_map(fn (BoardCategory $c) => $c->label(), BoardCategory::cases());

    expect(array_unique($labels))->toHaveCount(count($labels));
});
