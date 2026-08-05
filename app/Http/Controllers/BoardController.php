<?php

namespace App\Http\Controllers;

use App\Actions\CreateBoardAction;
use App\Actions\DeleteBoardAction;
use App\Actions\UpdateBoardAction;
use App\Enums\BoardCategory;
use App\Http\Requests\CreateBoardRequest;
use App\Http\Requests\UpdateBoardRequest;
use App\Models\Board;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BoardController extends Controller
{
    public function __construct(
        private readonly CreateBoardAction $createBoardAction,
        private readonly UpdateBoardAction $updateBoardAction,
        private readonly DeleteBoardAction $deleteBoardAction,
    ) {}

    public function create(): View
    {
        return view('boards.create', [
            'categories' => BoardCategory::cases(),
        ]);
    }

    public function store(CreateBoardRequest $request): RedirectResponse
    {
        $board = $this->createBoardAction->execute(
            $request->user(),
            $request->validated()
        );

        return redirect()
            ->route('boards.show', $board)
            ->with('status', 'board-created');
    }

    public function show(Board $board): View
    {
        return view('boards.show', [
            'board' => $board,
        ]);
    }

    public function edit(Board $board): View
    {
        return view('boards.edit', [
            'board' => $board,
            'categories' => BoardCategory::cases(),
        ]);
    }

    public function update(UpdateBoardRequest $request, Board $board): RedirectResponse
    {
        $this->updateBoardAction->execute($board, $request->validated());

        return redirect()
            ->route('boards.show', $board)
            ->with('status', 'board-updated');
    }

    public function destroy(Board $board): RedirectResponse
    {
        $this->deleteBoardAction->execute($board);

        return redirect()
            ->route('dashboard')
            ->with('status', 'board-deleted');
    }
}
