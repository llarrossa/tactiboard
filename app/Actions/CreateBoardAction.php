<?php

namespace App\Actions;

use App\Models\Board;
use App\Models\User;

class CreateBoardAction
{
    /**
     * Cria uma prancheta para o usuário informado.
     *
     * A propriedade vem do usuário autenticado, nunca dos dados de entrada —
     * por isso a associação é explícita e `user_id` está fora do fillable.
     *
     * @param  array{title: string, description?: string|null, category: string}  $data
     */
    public function execute(User $user, array $data): Board
    {
        $board = new Board($data);

        $board->user()->associate($user);
        $board->save();

        return $board;
    }
}
