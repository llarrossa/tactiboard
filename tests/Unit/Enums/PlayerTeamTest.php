<?php

use App\Enums\PlayerTeam;

test('os valores persistidos sao home e away', function () {
    // O lado vai para dentro de canvas_data (docs/03 secao 6.1), entao mudar
    // um destes valores invalidaria toda prancheta ja gravada.
    expect(PlayerTeam::values())->toBe(['home', 'away']);
});

test('cada lado tem um rotulo', function () {
    foreach (PlayerTeam::cases() as $team) {
        expect($team->label())->toBeString()->not->toBeEmpty();
    }
});

test('os rotulos nao se repetem', function () {
    // O rotulo e o que distingue o proprio time do adversario na toolbar e no
    // painel; dois iguais deixariam o usuario sem saber que botao aperta.
    $labels = array_map(fn (PlayerTeam $t) => $t->label(), PlayerTeam::cases());

    expect(array_unique($labels))->toHaveCount(count($labels));
});
