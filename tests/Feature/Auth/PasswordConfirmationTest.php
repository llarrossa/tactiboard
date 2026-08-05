<?php

use App\Models\User;

test('a tela de confirmacao de senha e exibida', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/confirm-password');

    $response->assertStatus(200);
});

test('a senha e confirmada', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/confirm-password', [
        'password' => 'password',
    ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();
    // O efeito real da confirmacao e a marca de tempo na sessao.
    $response->assertSessionHas('auth.password_confirmed_at');
});

test('a senha nao e confirmada com valor incorreto', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/confirm-password', [
        'password' => 'senha-errada',
    ]);

    $response->assertSessionHasErrors('password');
    $response->assertSessionMissing('auth.password_confirmed_at');
});

test('a confirmacao exige a senha', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/confirm-password', []);

    $response->assertSessionHasErrors('password');
    $response->assertSessionMissing('auth.password_confirmed_at');
});

test('um visitante nao acessa a confirmacao de senha', function () {
    $this->get('/confirm-password')->assertRedirect(route('login'));
    $this->post('/confirm-password', ['password' => 'password'])
        ->assertRedirect(route('login'));
});
