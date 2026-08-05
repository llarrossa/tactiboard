<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('a tela de recuperacao de senha e exibida', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('o link de redefinicao pode ser solicitado', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('a solicitacao exige um email valido', function () {
    Notification::fake();

    $response = $this->post('/forgot-password', ['email' => 'nao-e-um-email']);

    $response->assertSessionHasErrors('email');
    Notification::assertNothingSent();
});

test('a tela de nova senha e exibida', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('a senha e redefinida com um token valido', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'nova-senha-forte',
            'password_confirmation' => 'nova-senha-forte',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        // A senha precisa ficar utilizavel de fato, nao apenas redirecionar sem erro.
        expect(Hash::check('nova-senha-forte', $user->refresh()->password))->toBeTrue();

        return true;
    });
});

test('a senha nao e redefinida com um token invalido', function () {
    $user = User::factory()->create();

    $response = $this->post('/reset-password', [
        'token' => 'token-invalido',
        'email' => $user->email,
        'password' => 'nova-senha-forte',
        'password_confirmation' => 'nova-senha-forte',
    ]);

    $response->assertSessionHasErrors('email');
    expect(Hash::check('nova-senha-forte', $user->refresh()->password))->toBeFalse();
});

test('a redefinicao recusa confirmacao de senha divergente', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'nova-senha-forte',
            'password_confirmation' => 'outra-senha',
        ]);

        $response->assertSessionHasErrors('password');
        expect(Hash::check('nova-senha-forte', $user->refresh()->password))->toBeFalse();

        return true;
    });
});

test('um usuario autenticado nao acessa as telas de redefinicao', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/forgot-password')
        ->assertRedirect(route('dashboard', absolute: false));

    $this->actingAs($user)->get('/reset-password/qualquer-token')
        ->assertRedirect(route('dashboard', absolute: false));
});
