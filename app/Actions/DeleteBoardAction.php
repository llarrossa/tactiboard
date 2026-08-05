<?php

namespace App\Actions;

use App\Models\Board;

class DeleteBoardAction
{
    /**
     * Exclui a prancheta definitivamente.
     *
     * O MVP não usa Soft Delete (docs/03 §10): a exclusão precisa impedir o
     * acesso futuro à análise (RF-008).
     */
    public function execute(Board $board): void
    {
        $board->delete();
    }
}
