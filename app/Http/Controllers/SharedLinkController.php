<?php

namespace App\Http\Controllers;

use App\Actions\GenerateSharedLinkAction;
use App\Actions\RevokeSharedLinkAction;
use App\Models\Board;
use Illuminate\Http\RedirectResponse;

/**
 * Gerencia o link publico de uma prancheta, do lado do dono.
 *
 * A visualizacao publica e outra fronteira, anonima, e vive no
 * SharedBoardController.
 */
class SharedLinkController extends Controller
{
    public function __construct(
        private readonly GenerateSharedLinkAction $generateSharedLinkAction,
        private readonly RevokeSharedLinkAction $revokeSharedLinkAction,
    ) {}

    public function store(Board $board): RedirectResponse
    {
        $this->generateSharedLinkAction->execute($board);

        return redirect()
            ->route('boards.show', $board)
            ->with('status', 'board-shared');
    }

    public function destroy(Board $board): RedirectResponse
    {
        $this->revokeSharedLinkAction->execute($board);

        return redirect()
            ->route('boards.show', $board)
            ->with('status', 'board-unshared');
    }
}
