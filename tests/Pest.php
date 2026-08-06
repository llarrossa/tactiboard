<?php

use App\Livewire\BoardEditor;
use App\Models\Board;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
 * Os testes unitarios tambem sobem a aplicacao, mas sem banco: classes isoladas
 * como os enums usam o tradutor em label(), e o tradutor precisa do container.
 * O RefreshDatabase continua restrito a Feature, entao Unit segue rapido.
 */
pest()->extend(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/*
 * Ajudantes do canvas.
 *
 * Vivem aqui, e nao dentro de um arquivo de teste, porque varios arquivos os
 * usam: monta o editor sobre uma prancheta do proprio usuario e descreve
 * elementos validos sem repetir o schema de docs/03 secao 6.1 em cada teste.
 */

/**
 * @param  array<int, array<string, mixed>>  $elements
 */
function editorWith(array $elements, ?User $user = null): Testable
{
    $user ??= User::factory()->create();

    return Livewire\Livewire::actingAs($user)->test(BoardEditor::class, [
        'board' => Board::factory()->for($user)->create([
            'canvas_data' => ['elements' => $elements],
        ]),
    ]);
}

/**
 * @return array<string, mixed>
 */
function ball(string $id = 'b1', float $x = 500, float $y = 300): array
{
    return ['id' => $id, 'type' => 'ball', 'x' => $x, 'y' => $y];
}

/**
 * @return array<string, mixed>
 */
function arrow(string $id = 'a1'): array
{
    return ['id' => $id, 'type' => 'arrow', 'start' => ['x' => 100, 'y' => 100], 'end' => ['x' => 200, 'y' => 200]];
}
