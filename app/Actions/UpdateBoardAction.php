<?php

namespace App\Actions;

use App\Models\Board;

class UpdateBoardAction
{
    /**
     * Atualiza os dados descritivos da prancheta.
     *
     * O canvas não passa por aqui: ele tem fluxo próprio no editor e ganha a
     * sua Action na Fase 3 (UpdateBoardCanvasAction).
     *
     * @param  array{title: string, description?: string|null, category: string}  $data
     */
    public function execute(Board $board, array $data): Board
    {
        $board->update($data);

        return $board;
    }
}
