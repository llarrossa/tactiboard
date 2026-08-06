<?php

namespace App\Livewire;

use App\Models\Board;
use Illuminate\View\View;
use Livewire\Component;

/**
 * Editor tatico da prancheta (Fase 3).
 *
 * O componente e montado na tela da prancheta e mantem o estado do canvas
 * enquanto o usuario edita. A autorizacao nao e refeita aqui: a rota que
 * renderiza a tela ja passa pela BoardPolicy via `can:view,board`.
 */
class BoardEditor extends Component
{
    public Board $board;

    public function mount(Board $board): void
    {
        $this->board = $board;
    }

    public function render(): View
    {
        return view('livewire.board-editor');
    }
}
