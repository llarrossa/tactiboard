<?php

namespace App\Livewire;

use App\Actions\UpdateBoardCanvasAction;
use App\Enums\CanvasElementType;
use App\Enums\PlayerTeam;
use App\Models\Board;
use App\Rules\CanvasRules;
use Illuminate\Support\Str;
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

    public function addPlayer(string $team): void
    {
        // O lado vem do botao, mas chega pelo navegador: so os valores do enum
        // sao aceitos.
        $side = PlayerTeam::tryFrom($team);

        abort_if($side === null, 400, __('This element type does not exist.'));

        $this->push([
            'type' => CanvasElementType::Player->value,
            'team' => $side->value,
            'number' => $this->nextNumber($side),
            ...$this->spawnAt($side === PlayerTeam::Home ? 350 : 700, 340),
        ]);
    }

    public function addBall(): void
    {
        $this->push([
            'type' => CanvasElementType::Ball->value,
            ...$this->spawnAt(525, 340),
        ]);
    }

    public function addCone(): void
    {
        $this->push([
            'type' => CanvasElementType::Cone->value,
            ...$this->spawnAt(525, 200),
        ]);
    }

    public function addText(): void
    {
        $this->push([
            'type' => CanvasElementType::Text->value,
            'content' => __('Note'),
            ...$this->spawnAt(525, 110),
        ]);
    }

    public function addArrow(): void
    {
        $this->push([
            'type' => CanvasElementType::Arrow->value,
            'start' => $this->spawnAt(420, 480),
            'end' => $this->spawnAt(620, 380),
        ]);
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
        return view('livewire.board-editor', [
            // Desenha so o que e desenhavel. Um elemento malformado — que so
            // aparece se a validacao reprovou ou se o payload foi adulterado —
            // fica de fora do campo, e a mensagem de erro explica o que houve.
            'drawable' => CanvasRules::drawable($this->elements),
        ]);
    }

    /**
     * @param  array<string, mixed>  $element
     */
    private function push(array $element): void
    {
        $this->authorize('update', $this->board);

        if (count($this->elements) >= CanvasRules::MAX_ELEMENTS) {
            $this->addError('elements', __('A board holds at most :max elements.', [
                'max' => CanvasRules::MAX_ELEMENTS,
            ]));

            return;
        }

        // O id acompanha o elemento por toda a vida dele: e o que permite
        // arrastar e remover sem depender da posicao na lista.
        $this->elements[] = ['id' => Str::random(10), ...$element];
    }

    /**
     * Menor numero livre do lado, para que o usuario nao precise escolher um a
     * cada jogador adicionado.
     */
    private function nextNumber(PlayerTeam $team): int
    {
        $used = [];

        foreach ($this->elements as $element) {
            if (($element['type'] ?? null) === CanvasElementType::Player->value
                && ($element['team'] ?? null) === $team->value) {
                $used[] = (int) ($element['number'] ?? 0);
            }
        }

        for ($number = 1; $number < 99; $number++) {
            if (! in_array($number, $used, strict: true)) {
                return $number;
            }
        }

        return 99;
    }

    /**
     * Ponto onde um elemento novo nasce, deslocado em cascata para nao
     * empilhar exatamente sobre o anterior.
     *
     * @return array{x: float, y: float}
     */
    private function spawnAt(float $x, float $y): array
    {
        $step = (count($this->elements) % 6) * 25;

        return [
            'x' => min($x + $step, (float) CanvasRules::FIELD_WIDTH),
            'y' => min($y + $step, (float) CanvasRules::FIELD_HEIGHT),
        ];
    }
}
