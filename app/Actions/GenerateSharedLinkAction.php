<?php

namespace App\Actions;

use App\Enums\BoardVisibility;
use App\Models\Board;
use App\Models\SharedLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateSharedLinkAction
{
    /**
     * Comprimento do token. 32 caracteres alfanumericos nao expoem o id
     * interno e sao inviaveis de adivinhar por tentativa (docs/03 §7.1).
     */
    private const TOKEN_LENGTH = 32;

    /**
     * Compartilha a prancheta (RF-014).
     *
     * Gerar o link e tornar a prancheta publica sao dois mecanismos separados
     * no banco (docs/03 §6.2), mas uma unica operacao para o usuario: entregar
     * um token sem tornar a prancheta publica devolveria um link que nao abre.
     *
     * O token existente e reaproveitado de proposito — recompartilhar nao pode
     * invalidar a URL que o usuario ja enviou para outras pessoas.
     */
    public function execute(Board $board): SharedLink
    {
        return DB::transaction(function () use ($board): SharedLink {
            // A prancheta e travada antes da leitura para serializar dois
            // compartilhamentos simultaneos. Sem isso, um duplo clique cria
            // dois tokens validos para a mesma prancheta: ambas as requisicoes
            // leem "nenhum link" e ambas inserem. O dono passaria a ter uma URL
            // publica que ele nao ve no painel e nao sabe revogar.
            $board->newQuery()->whereKey($board->getKey())->lockForUpdate()->first();

            $link = $board->sharedLinks()->first();

            if ($link === null) {
                $link = new SharedLink(['token' => Str::random(self::TOKEN_LENGTH)]);

                $link->board()->associate($board);
                $link->save();
            }

            $board->update(['visibility' => BoardVisibility::Public]);

            return $link;
        });
    }
}
