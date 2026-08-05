<?php

use App\Enums\BoardVisibility;

test('os valores persistidos sao private e public', function () {
    expect(BoardVisibility::values())->toBe(['private', 'public']);
});

test('cada visibilidade tem um rotulo', function () {
    foreach (BoardVisibility::cases() as $visibility) {
        expect($visibility->label())->toBeString()->not->toBeEmpty();
    }
});
