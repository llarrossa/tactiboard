<?php

namespace App\Actions;

use App\Models\Board;
use App\Models\SharedLink;
use Illuminate\Support\Facades\DB;

class RotateSharedLinkAction
{
    /**
     * Troca o token do link publico da prancheta (Fase 5).
     *
     * Existe por causa da limitacao registrada em docs/03 §7.1: compartilhar
     * de novo reaproveita o token, entao um link que vaze continua o mesmo. A
     * revogacao derruba o acesso enquanto a prancheta estiver privada, mas nao
     * aposenta o endereco. Esta operacao aposenta.
     *
     * A visibilidade nao e tocada de proposito. Os dois mecanismos seguem
     * separados (docs/03 §6.2): aqui muda *por onde* o acesso acontece, nao
     * *se* ele pode acontecer. Uma prancheta publica continua publica, no
     * endereco novo.
     */
    public function execute(Board $board): SharedLink
    {
        return DB::transaction(function () use ($board): SharedLink {
            // Mesma trava de GenerateSharedLinkAction, e pelo mesmo motivo: sem
            // ela, dois cliques simultaneos numa prancheta ainda sem link
            // inseririam duas linhas, e o dono passaria a ter uma URL publica
            // que nao aparece no painel e que ele nao sabe revogar.
            $board->newQuery()->whereKey($board->getKey())->lockForUpdate()->first();

            $link = $board->sharedLinks()->first();

            if ($link === null) {
                $link = new SharedLink;
                $link->board()->associate($board);
            }

            $link->token = SharedLink::newToken();
            $link->save();

            return $link;
        });
    }
}
