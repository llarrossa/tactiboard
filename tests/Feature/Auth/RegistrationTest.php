<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('a tela de cadastro e exibida', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('um novo usuario consegue se cadastrar', function () {
    $response = $this->post('/register', [
        'name' => 'Treinador Teste',
        'email' => 'treinador@tactiboard.test',
        'password' => 'senha-de-teste',
        'password_confirmation' => 'senha-de-teste',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::firstWhere('email', 'treinador@tactiboard.test');

    expect($user)->not->toBeNull()
        ->and($user->name)->toBe('Treinador Teste');
});

test('a senha e gravada com hash', function () {
    $this->post('/register', [
        'name' => 'Treinador Teste',
        'email' => 'treinador@tactiboard.test',
        'password' => 'senha-de-teste',
        'password_confirmation' => 'senha-de-teste',
    ]);

    $user = User::firstWhere('email', 'treinador@tactiboard.test');

    expect($user->password)->not->toBe('senha-de-teste')
        ->and(Hash::check('senha-de-teste', $user->password))->toBeTrue();
});

test('o cadastro exige nome, email e senha', function () {
    $response = $this->post('/register', []);

    $response->assertSessionHasErrors(['name', 'email', 'password']);
    $this->assertGuest();
});

test('o cadastro recusa email ja utilizado', function () {
    User::factory()->create(['email' => 'treinador@tactiboard.test']);

    $response = $this->post('/register', [
        'name' => 'Outro Treinador',
        'email' => 'treinador@tactiboard.test',
        'password' => 'senha-de-teste',
        'password_confirmation' => 'senha-de-teste',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
    expect(User::where('email', 'treinador@tactiboard.test')->count())->toBe(1);
});

test('o cadastro recusa confirmacao de senha divergente', function () {
    $response = $this->post('/register', [
        'name' => 'Treinador Teste',
        'email' => 'treinador@tactiboard.test',
        'password' => 'senha-de-teste',
        'password_confirmation' => 'outra-senha',
    ]);

    $response->assertSessionHasErrors('password');
    $this->assertGuest();
    expect(User::count())->toBe(0);
});

test('um usuario autenticado nao acessa a tela de cadastro', function () {
    $response = $this->actingAs(User::factory()->create())->get('/register');

    $response->assertRedirect(route('dashboard', absolute: false));
});
