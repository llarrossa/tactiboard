<?php

namespace App\Actions;

use App\Enums\BoardVisibility;
use App\Models\Board;

class RevokeSharedLinkAction
{
    /**
     * Deixa de compartilhar a prancheta.
     *
     * O link nao e apagado: docs/03 §6.2 define que tornar a prancheta privada
     * revoga o acesso de todos os links existentes *sem remove-los*. Assim o
     * dono volta a compartilhar depois com a mesma URL.
     */
    public function execute(Board $board): void
    {
        $board->update(['visibility' => BoardVisibility::Private]);
    }
}
