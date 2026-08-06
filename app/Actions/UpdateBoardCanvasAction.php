<?php

namespace App\Actions;

use App\Models\Board;
use App\Rules\CanvasRules;

class UpdateBoardCanvasAction
{
    /**
     * Grava o estado visual da prancheta (RF-013).
     *
     * Recebe os elementos ja validados e os normaliza antes de gravar. O
     * array e reindexado porque remover um elemento no meio da lista deixa
     * buracos nas chaves, e um array PHP com buracos vira objeto em JSON —
     * o que quebraria a leitura na proxima abertura do editor.
     *
     * @param  array<int, array<string, mixed>>  $elements
     */
    public function execute(Board $board, array $elements): Board
    {
        $board->update([
            'canvas_data' => ['elements' => CanvasRules::normalize($elements)],
        ]);

        return $board;
    }
}
