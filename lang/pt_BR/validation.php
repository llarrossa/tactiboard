<?php

/*
 * Traducoes parciais: apenas as regras alcancaveis pelas telas existentes.
 * As chaves ausentes caem no APP_FALLBACK_LOCALE (en), entao nao ha risco de
 * mensagem vazia — o arquivo cresce conforme novas regras entram no produto.
 */

return [

    'confirmed' => 'A confirmação de :attribute não confere.',
    'current_password' => 'A senha informada está incorreta.',
    'email' => 'Informe um :attribute válido.',
    'lowercase' => 'O campo :attribute deve conter apenas letras minúsculas.',
    'max' => [
        'string' => 'O campo :attribute não pode ter mais que :max caracteres.',
    ],
    'min' => [
        'string' => 'O campo :attribute deve ter no mínimo :min caracteres.',
    ],
    'password' => [
        'letters' => 'O campo :attribute deve conter ao menos uma letra.',
        'mixed' => 'O campo :attribute deve conter ao menos uma letra maiúscula e uma minúscula.',
        'numbers' => 'O campo :attribute deve conter ao menos um número.',
        'symbols' => 'O campo :attribute deve conter ao menos um símbolo.',
        'uncompromised' => 'O :attribute informado apareceu em um vazamento de dados. Escolha outro.',
    ],
    'required' => 'O campo :attribute é obrigatório.',
    'string' => 'O campo :attribute deve ser um texto.',
    'unique' => 'Este :attribute já está em uso.',

    'attributes' => [
        'current_password' => 'senha atual',
        'email' => 'e-mail',
        'name' => 'nome',
        'password' => 'senha',
        'password_confirmation' => 'confirmação de senha',
    ],

];
