<?php

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Event;

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

test('cinco tentativas erradas bloqueiam o login', function () {
    // O bloqueio protege a senha de tentativa em massa. Sem teste, uma
    // reinstalacao do Breeze ou uma mudanca no Form Request poderia derrubar a
    // protecao sem que nada na suite acusasse.
    Event::fake([Lockout::class]);

    $user = User::factory()->create();

    foreach (range(1, 5) as $tentativa) {
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'senha-errada',
        ]);
    }

    // A senha correta tambem e recusada: o que bloqueia e a quantidade de
    // tentativas, nao a credencial enviada.
    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();

    Event::assertDispatched(Lockout::class);
});

test('o bloqueio avisa quanto tempo falta', function () {
    // O relogio e congelado para que a espera restante seja exatamente a
    // janela do limitador: sem isso o segundo que passa entre a tentativa e a
    // assercao ja muda a mensagem.
    $this->freezeTime();

    $user = User::factory()->create();

    foreach (range(1, 6) as $tentativa) {
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'senha-errada',
        ]);
    }

    // A mensagem e outra: 'Muitas tentativas de acesso...', e nao o
    // 'E-mail ou senha incorretos' das tentativas anteriores.
    $response->assertSessionHasErrorsIn('default', [
        'email' => trans('auth.throttle', ['seconds' => 60, 'minutes' => 1]),
    ]);
});

test('o bloqueio vale por email e endereco, nao para o site inteiro', function () {
    // Bloquear o login de todo mundo porque uma conta sofreu tentativa seria
    // negacao de servico de graca.
    $alvo = User::factory()->create();
    $outro = User::factory()->create();

    foreach (range(1, 6) as $tentativa) {
        $this->post('/login', [
            'email' => $alvo->email,
            'password' => 'senha-errada',
        ]);
    }

    $response = $this->post('/login', [
        'email' => $outro->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticatedAs($outro);
    $response->assertRedirect(route('dashboard', absolute: false));
});
