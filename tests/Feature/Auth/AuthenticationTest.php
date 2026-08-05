<?php

use App\Models\User;

test('a tela de login e exibida', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('um usuario consegue entrar pela tela de login', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('um usuario nao entra com a senha errada', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'senha-errada',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('um usuario nao entra com email inexistente', function () {
    $response = $this->post('/login', [
        'email' => 'ninguem@tactiboard.test',
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('o login exige email e senha', function () {
    $response = $this->post('/login', []);

    $response->assertSessionHasErrors(['email', 'password']);
    $this->assertGuest();
});

test('um usuario consegue sair', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('um usuario autenticado nao acessa a tela de login', function () {
    $response = $this->actingAs(User::factory()->create())->get('/login');

    $response->assertRedirect(route('dashboard', absolute: false));
});
