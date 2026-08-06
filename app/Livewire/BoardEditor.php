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
    /**
     * Deslocamento da copia em relacao ao original, em unidades de campo
     * (3 metros). Longe o bastante para a copia nao esconder a origem, perto
     * o bastante para continuar sendo a mesma jogada.
     */
    private const DUPLICATE_SHIFT = 30;

    public Board $board;

    /**
     * Estado do canvas em edicao.
     *
     * @var array<int, array<string, mixed>>
     */
    public array $elements = [];

    /**
     * Elemento selecionado, pelo id. O painel de propriedades acompanha ele.
     */
    public ?string $selectedId = null;

    /**
     * Se existe edicao ainda nao gravada.
     *
     * E uma marca posta por quem altera o canvas, e nao uma comparacao com o
     * que esta no banco: o clamp devolve float onde o registro gravado pode
     * ter inteiro, e comparar os dois acusaria mudanca em elemento parado.
     */
    public bool $hasUnsavedChanges = false;

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

    /**
     * Marca o elemento em que o usuario clicou. Passar null limpa a selecao.
     */
    public function select(?string $id): void
    {
        $this->selectedId = $id !== null && $this->indexOf($id) !== null ? $id : null;
    }

    /**
     * Move um elemento pelo deslocamento vindo do arrasto.
     *
     * Recebe delta, e nao posicao absoluta: o cliente diz o quanto arrastou e
     * o servidor, que ja sabe onde o elemento estava, decide onde ele para.
     *
     * @param  string|null  $part  `start` ou `end` para arrastar uma ponta de
     *                             seta; null move o elemento inteiro.
     */
    public function moveElement(string $id, float $dx, float $dy, ?string $part = null): void
    {
        $this->authorize('update', $this->board);

        $index = $this->indexOf($id);

        if ($index === null) {
            return;
        }

        // indexOf() so devolve indice de elemento que e array: um valor
        // adulterado na lista nunca chega aqui, e nao precisa de guarda.
        $element = $this->elements[$index];

        $isArrowEnd = ($element['type'] ?? null) === CanvasElementType::Arrow->value
            && in_array($part, ['start', 'end'], true);

        if ($isArrowEnd) {
            $element[$part] = CanvasRules::clamp(
                (float) ($element[$part]['x'] ?? 0) + $dx,
                (float) ($element[$part]['y'] ?? 0) + $dy,
            );
        } else {
            $element = $this->shifted($element, $dx, $dy);
        }

        // Uma peca ja presa na borda nao se move: marcar alteracao aqui
        // pediria confirmacao de saida por um arrasto que nao mudou nada.
        if ($this->positionOf($element) !== $this->positionOf($this->elements[$index])) {
            $this->hasUnsavedChanges = true;
        }

        $this->elements[$index] = $element;
        $this->selectedId = $id;
    }

    public function removeElement(string $id): void
    {
        $this->authorize('update', $this->board);

        $index = $this->indexOf($id);

        if ($index === null) {
            return;
        }

        unset($this->elements[$index]);

        $this->elements = array_values($this->elements);
        $this->hasUnsavedChanges = true;

        if ($this->selectedId === $id) {
            $this->selectedId = null;
        }
    }

    /**
     * Esvazia o campo.
     *
     * Nao grava sozinho: como toda edicao do editor, a mudanca so vira
     * canvas_data quando o usuario salva. Recarregar a pagina antes disso
     * traz a jogada de volta, o que e o caminho de arrependimento mais
     * simples enquanto nao existe desfazer.
     */
    public function clearCanvas(): void
    {
        $this->authorize('update', $this->board);

        // Um campo ja vazio nao muda com a limpeza, e nao ha o que avisar.
        $this->hasUnsavedChanges = $this->hasUnsavedChanges || $this->elements !== [];

        $this->elements = [];
        $this->selectedId = null;

        // Um erro sobre um elemento que acabou de sair do campo nao descreve
        // mais nada que o usuario possa corrigir.
        $this->resetErrorBag();
    }

    /**
     * Cria uma copia do elemento, deslocada para nao esconder o original.
     *
     * A selecao passa para a copia: duplicar de novo empilha a jogada em
     * cascata, que e o que o usuario espera ao repetir o atalho.
     */
    public function duplicateElement(string $id): void
    {
        // Autoriza antes de qualquer coisa, como as demais escritas. Deixar a
        // verificacao por conta do push() faria a resposta depender do estado
        // do canvas: id inexistente devolveria 200 a quem perdeu o acesso.
        $this->authorize('update', $this->board);

        $index = $this->indexOf($id);

        if ($index === null) {
            return;
        }

        $element = $this->elements[$index];

        $copy = $this->shifted($element, self::DUPLICATE_SHIFT, self::DUPLICATE_SHIFT);

        // Junto da borda o deslocamento e invertido: preso pelo limite do
        // campo, a copia pararia exatamente sobre o elemento de origem e o
        // usuario nao veria nada acontecer.
        if ($this->positionOf($copy) === $this->positionOf($element)) {
            $copy = $this->shifted($element, -self::DUPLICATE_SHIFT, -self::DUPLICATE_SHIFT);
        }

        // O id nao se copia: ele identifica o elemento, e dois elementos com o
        // mesmo id fariam o arrasto mover a peca errada.
        unset($copy['id']);

        // Dois jogadores do mesmo lado com o mesmo numero seriam a mesma peca
        // duas vezes no campo. A copia recebe o proximo numero livre.
        $side = ($copy['type'] ?? null) === CanvasElementType::Player->value
            ? PlayerTeam::tryFrom($copy['team'] ?? '')
            : null;

        if ($side !== null) {
            $copy['number'] = $this->nextNumber($side);
        }

        $copyId = $this->push($copy);

        if ($copyId !== null) {
            $this->selectedId = $copyId;
        }
    }

    /**
     * Valida a propriedade recem-editada no painel, para que o usuario veja o
     * problema na hora em vez de descobrir so ao salvar.
     *
     * A condicao cobre tambem a escrita na lista inteira (`elements`), e nao
     * so os caminhos aninhados: o navegador pode substituir a propriedade de
     * uma vez, e essa tambem e uma edicao por gravar.
     */
    public function updated(string $property): void
    {
        if ($property !== 'elements' && ! str_starts_with($property, 'elements.')) {
            return;
        }

        $this->hasUnsavedChanges = true;

        // Escrever na lista inteira exige validar o canvas inteiro: o
        // validateOnly('elements') so alcanca as regras da raiz, e um elemento
        // malformado sairia do desenho por drawable() sem mensagem que
        // explicasse o sumico.
        if ($property === 'elements') {
            $this->validate(CanvasRules::rules(), CanvasRules::messages());

            return;
        }

        $this->validateOnly($property, CanvasRules::rules(), CanvasRules::messages());
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
        $this->hasUnsavedChanges = false;

        $this->dispatch('canvas-saved');
    }

    public function render(): View
    {
        // Desenha so o que e desenhavel. Um elemento malformado — que so
        // aparece se a validacao reprovou ou se o payload foi adulterado —
        // fica de fora do campo, e a mensagem de erro explica o que houve.
        $drawable = CanvasRules::drawable($this->elements);

        return view('livewire.board-editor', [
            'drawable' => $drawable,
            // O painel abre pelo indice na lista original, que e o caminho que
            // os campos escrevem — mas so quando o elemento selecionado esta
            // integro, senao o painel quebraria junto com ele.
            'selectedIndex' => in_array($this->selectedId, array_column($drawable, 'id'), true)
                ? $this->indexOf($this->selectedId)
                : null,
        ]);
    }

    /**
     * Posicao do elemento na lista, ou null se ele nao existe mais.
     */
    private function indexOf(string $id): ?int
    {
        foreach ($this->elements as $index => $element) {
            if (is_array($element) && ($element['id'] ?? null) === $id) {
                return (int) $index;
            }
        }

        return null;
    }

    /**
     * Recorta o deslocamento de uma seta para que as duas pontas continuem
     * dentro do campo, preservando comprimento e direcao.
     *
     * @param  array<string, mixed>  $arrow
     * @return array{0: float, 1: float}
     */
    private function fittedShift(array $arrow, float $dx, float $dy): array
    {
        $xs = [(float) ($arrow['start']['x'] ?? 0), (float) ($arrow['end']['x'] ?? 0)];
        $ys = [(float) ($arrow['start']['y'] ?? 0), (float) ($arrow['end']['y'] ?? 0)];

        return [
            max(-min($xs), min($dx, CanvasRules::FIELD_WIDTH - max($xs))),
            max(-min($ys), min($dy, CanvasRules::FIELD_HEIGHT - max($ys))),
        ];
    }

    /**
     * Desloca o elemento inteiro, preso dentro do campo.
     *
     * Mover a seta limita o proprio deslocamento, e nao cada ponta em
     * separado: prender as pontas uma a uma faria a seta encolher ao encostar
     * na borda, mudando a jogada que o usuario desenhou.
     *
     * @param  array<string, mixed>  $element
     * @return array<string, mixed>
     */
    private function shifted(array $element, float $dx, float $dy): array
    {
        if (($element['type'] ?? null) !== CanvasElementType::Arrow->value) {
            return [...$element, ...CanvasRules::clamp(
                (float) ($element['x'] ?? 0) + $dx,
                (float) ($element['y'] ?? 0) + $dy,
            )];
        }

        [$dx, $dy] = $this->fittedShift($element, $dx, $dy);

        foreach (['start', 'end'] as $end) {
            $element[$end] = CanvasRules::clamp(
                (float) ($element[$end]['x'] ?? 0) + $dx,
                (float) ($element[$end]['y'] ?? 0) + $dy,
            );
        }

        return $element;
    }

    /**
     * Onde o elemento esta, no formato do proprio tipo. Serve para comparar
     * duas versoes do mesmo elemento sem depender das demais chaves.
     *
     * As coordenadas sao convertidas para float de proposito: um canvas
     * gravado pode trazer `100` onde o clamp devolve `100.0`, e a comparacao
     * estrita diria que o elemento se moveu quando ele esta parado.
     *
     * @param  array<string, mixed>  $element
     * @return array<int, float>
     */
    private function positionOf(array $element): array
    {
        if (($element['type'] ?? null) === CanvasElementType::Arrow->value) {
            return [
                (float) ($element['start']['x'] ?? 0),
                (float) ($element['start']['y'] ?? 0),
                (float) ($element['end']['x'] ?? 0),
                (float) ($element['end']['y'] ?? 0),
            ];
        }

        return [(float) ($element['x'] ?? 0), (float) ($element['y'] ?? 0)];
    }

    /**
     * Acrescenta um elemento ao canvas e devolve o id dele, ou null quando o
     * limite da prancheta ja foi atingido.
     *
     * @param  array<string, mixed>  $element
     */
    private function push(array $element): ?string
    {
        $this->authorize('update', $this->board);

        if (count($this->elements) >= CanvasRules::MAX_ELEMENTS) {
            $this->addError('elements', __('A board holds at most :max elements.', [
                'max' => CanvasRules::MAX_ELEMENTS,
            ]));

            return null;
        }

        // O id acompanha o elemento por toda a vida dele: e o que permite
        // arrastar e remover sem depender da posicao na lista.
        $id = Str::random(10);

        $this->elements[] = ['id' => $id, ...$element];
        $this->hasUnsavedChanges = true;

        return $id;
    }

    /**
     * Menor numero livre do lado, para que o usuario nao precise escolher um a
     * cada jogador adicionado.
     */
    private function nextNumber(PlayerTeam $team): int
    {
        $used = [];

        foreach ($this->elements as $element) {
            if (is_array($element)
                && ($element['type'] ?? null) === CanvasElementType::Player->value
                && ($element['team'] ?? null) === $team->value) {
                $used[] = (int) ($element['number'] ?? 0);
            }
        }

        for ($number = 1; $number <= 99; $number++) {
            if (! in_array($number, $used, strict: true)) {
                return $number;
            }
        }

        // Os 99 numeros do lado estao em campo. Repetir o ultimo e melhor do
        // que recusar o elemento: o usuario ajusta o numero no painel.
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
