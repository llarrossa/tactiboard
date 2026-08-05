<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    /**
     * Cria a conta do novo usuario e dispara o evento de cadastro.
     *
     * O login da sessao nao acontece aqui: e responsabilidade da camada HTTP,
     * e manter a Action livre de sessao a torna reaproveitavel e testavel.
     *
     * @param  array{name: string, email: string, password: string}  $data
     */
    public function execute(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        return $user;
    }
}
