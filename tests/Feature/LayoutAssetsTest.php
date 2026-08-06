<?php

use App\Models\User;

/*
 * O Alpine deixou de ser importado no bundle do Vite na Fase 3: quem o fornece
 * agora e o Livewire. Estes testes protegem os dois lados dessa troca, porque a
 * falha e silenciosa — a pagina carrega normalmente e so o comportamento morre.
 */

test('o layout autenticado carrega o alpine pelo livewire', function () {
    $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

    $response->assertOk();
    // O prefixo da rota do Livewire tem um hash que muda entre instalacoes;
    // o nome do arquivo e a parte estavel.
    $response->assertSee('livewire.js', false);
});

test('o bundle do vite nao importa uma segunda instancia do alpine', function () {
    $bundle = file_get_contents(resource_path('js/app.js'));

    expect($bundle)->not->toContain("from 'alpinejs'")
        ->and($bundle)->not->toContain('Alpine.start()');
});

test('o dropdown da navbar continua declarado no layout autenticado', function () {
    $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

    $response->assertSee('x-data', false);
});
