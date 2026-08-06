<?php

namespace App\Actions;

use App\Models\Board;
use App\Models\SharedLink;

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
     *
     * Exige um link existente: nao ha endereco a aposentar numa prancheta que
     * nunca foi compartilhada, e criar um aqui deixaria um token gravado antes
     * de o dono pedir para compartilhar. O `firstOrFail` vira 404, que e a
     * resposta certa para uma operacao sobre algo que nao existe.
     *
     * Nao precisa da transacao com trava que GenerateSharedLinkAction usa: la
     * o risco e duas requisicoes simultaneas *inserirem* dois links; aqui a
     * unica escrita e um UPDATE na mesma linha, e dois pedidos ao mesmo tempo
     * apenas fazem o ultimo token valer.
     */
    public function execute(Board $board): SharedLink
    {
        $link = $board->sharedLinks()->firstOrFail();

        $link->token = SharedLink::newToken();
        $link->save();

        return $link;
    }
}
