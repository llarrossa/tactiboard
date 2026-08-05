<?php

use App\Models\User;

test('a pagina de perfil e exibida', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('os dados do perfil podem ser atualizados', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Treinador Teste',
            'email' => 'treinador@tactiboard.test',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    expect($user->name)->toBe('Treinador Teste')
        ->and($user->email)->toBe('treinador@tactiboard.test')
        ->and($user->email_verified_at)->toBeNull();
});

test('a verificacao de email permanece quando o email nao muda', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Treinador Teste',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('o perfil exige nome e email validos', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->patch('/profile', [
            'name' => '',
            'email' => 'nao-e-um-email',
        ]);

    $response->assertSessionHasErrors(['name', 'email']);
});

test('o perfil recusa email ja utilizado por outro usuario', function () {
    $outroUsuario = User::factory()->create(['email' => 'ocupado@tactiboard.test']);
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->patch('/profile', [
            'name' => $user->name,
            'email' => $outroUsuario->email,
        ]);

    $response->assertSessionHasErrors('email');
    expect($user->refresh()->email)->not->toBe('ocupado@tactiboard.test');
});

test('um usuario consegue excluir a propria conta', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('a exclusao da conta exige a senha correta', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'senha-errada',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    expect($user->fresh())->not->toBeNull();
});

test('um visitante nao acessa as rotas de perfil', function () {
    $user = User::factory()->create();

    $this->get('/profile')->assertRedirect(route('login'));
    $this->patch('/profile', ['name' => 'X', 'email' => 'x@tactiboard.test'])
        ->assertRedirect(route('login'));
    $this->delete('/profile', ['password' => 'password'])
        ->assertRedirect(route('login'));

    expect($user->fresh())->not->toBeNull();
});
