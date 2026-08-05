<?php

use App\Models\User;

test('um visitante e redirecionado para o login', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect(route('login'));
});

test('um usuario autenticado acessa o dashboard', function () {
    $user = User::factory()->create(['name' => 'Treinador Teste']);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee('Treinador Teste');
});

test('a pagina inicial e publica', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
