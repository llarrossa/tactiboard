<?php

namespace App\Livewire;

use App\Actions\UpdateBoardCanvasAction;
use App\Models\Board;
use App\Rules\CanvasRules;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Editor tatico da prancheta (Fase 3).
 *
 * O componente mantem os elementos do canvas enquanto o usuario edita e os
 * grava quando ele salva. A validacao usa CanvasRules, que e o ponto unico de
 * verdade sobre o formato — ver docs/04 secao 8.3.
 */
class BoardEditor extends Component
{
    public Board $board;

    /**
     * Estado do canvas em edicao.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $elements = [];

    public function mount(Board $board): void
    {
        // A rota que renderiza o editor ja passa pela BoardPolicy, mas o
        // componente nao depende disso: ele e uma fronteira propria e autoriza
        // tanto a leitura quanto a escrita.
        $this->authorize('view', $board);

        $this->board = $board;

        // canvas_data nunca e null: nasce {"elements": []} pelo model (docs/03
        // secao 6.1). O fallback cobre apenas registros gravados a mao.
        $this->elements = $board->canvas_data['elements'] ?? [];
    }

    public function save(UpdateBoardCanvasAction $action): void
    {
        $this->authorize('update', $this->board);

        // Remover um elemento deixa buracos nas chaves. Reindexar antes de
        // validar mantem os indices das mensagens de erro iguais aos que o
        // usuario ve na tela, e garante que o que se valida e o que se grava.
        $this->elements = array_values($this->elements);

        $this->validate(CanvasRules::rules(), CanvasRules::messages());

        $action->execute($this->board, $this->elements);

        // A normalizacao pode arredondar coordenadas e descartar chaves; a tela
        // passa a mostrar exatamente o que foi gravado.
        $this->elements = $this->board->canvas_data['elements'];

        $this->dispatch('canvas-saved');
    }

    public function render(): View
    {
        return view('livewire.board-editor');
    }
}
