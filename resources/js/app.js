import './bootstrap';

/*
 * O Alpine nao e importado aqui: quem o fornece e o Livewire, que embute a
 * propria copia e a inicializa. Manter o import manual do pacote `alpinejs`
 * faria duas instancias correrem na mesma pagina ("Detected multiple instances
 * of Alpine running") e quebraria o dropdown da navbar e os modais de exclusao.
 *
 * Decisao registrada na Fase 3. Ver docs/04 secao 8.2.
 */

/*
 * Arrastar elementos da prancheta.
 *
 * O arrasto e interacao local: seguir o ponteiro nao pode custar uma ida ao
 * servidor por pixel. Enquanto o usuario arrasta, o deslocamento vive so no
 * Alpine e e aplicado como um `transform` no elemento; ao soltar, a posicao
 * final vai para o Livewire, que e quem conhece o estado do canvas.
 *
 * O deslocamento e enviado como delta, e nao como posicao absoluta: o servidor
 * ja sabe onde o elemento esta e aplica o limite do campo por conta propria.
 *
 * A funcao e exposta no window em vez de registrada com Alpine.data porque o
 * bundle do Vite e um modulo com defer — ele executa antes do Livewire iniciar
 * o Alpine, mas depois de a pagina ser lida, o que tornaria o registro
 * dependente de ordem. Uma funcao global nao tem esse problema.
 */
window.tactiboardCanvasDrag = () => ({
    draggingId: null,
    draggingPart: null,
    origin: null,
    delta: { x: 0, y: 0 },

    /*
     * Deslocamentos ja enviados ao servidor que ainda nao voltaram, por id.
     *
     * O elemento continua desenhado no lugar onde o dedo o soltou enquanto a
     * resposta nao chega. Sem isso ele volta para a posicao antiga — que e a
     * que o desenho ainda tem — e so pula para a nova quando o Livewire
     * responde, o que na tela aparece como um solavanco.
     *
     * E um mapa por id, e nao um deslocamento so: arrastar outra peca antes de
     * a primeira assentar nao pode fazer a primeira piscar de volta.
     */
    settling: {},

    /*
     * Converte a posicao do ponteiro na tela para o sistema de coordenadas do
     * campo. O SVG escala por CSS, entao pixel de tela nao equivale a unidade
     * do campo; getScreenCTM devolve exatamente essa transformacao.
     */
    fieldPoint(event) {
        const svg = this.$refs.field;
        const point = svg.createSVGPoint();

        point.x = event.clientX;
        point.y = event.clientY;

        return point.matrixTransform(svg.getScreenCTM().inverse());
    },

    startDrag(event, id, part = null) {
        if (event.button !== undefined && event.button !== 0) {
            return;
        }

        this.draggingId = id;
        this.draggingPart = part;
        this.origin = this.fieldPoint(event);
        this.delta = { x: 0, y: 0 };
    },

    onMove(event) {
        if (this.draggingId === null) {
            return;
        }

        const point = this.fieldPoint(event);

        this.delta = {
            x: point.x - this.origin.x,
            y: point.y - this.origin.y,
        };
    },

    /*
     * O navegador pode tomar o ponteiro de volta no meio do gesto — o dedo sai
     * da tela, o sistema assume a rolagem, a janela perde o foco. Sem tratar o
     * cancelamento, o arrasto ficaria preso e todo movimento seguinte
     * continuaria deslocando o elemento.
     */
    cancelDrag() {
        this.draggingId = null;
        this.draggingPart = null;
        this.delta = { x: 0, y: 0 };
    },

    endDrag() {
        if (this.draggingId === null) {
            return;
        }

        const { x, y } = this.delta;
        const id = this.draggingId;
        const part = this.draggingPart;

        this.draggingId = null;
        this.draggingPart = null;
        this.delta = { x: 0, y: 0 };

        // Movimento menor que uma unidade do campo e clique, nao arrasto.
        if (Math.abs(x) < 1 && Math.abs(y) < 1) {
            this.$wire.select(id);

            return;
        }

        // O deslocamento sai do gesto e entra na espera: a peca nao se mexe da
        // largada ate o servidor confirmar onde ela para.
        this.settling[id] = { part, x, y };

        const settled = () => delete this.settling[id];

        // O mesmo tratamento para sucesso e para falha: se a chamada for
        // recusada (a sessao pode ter mudado com o editor aberto), segurar o
        // deslocamento deixaria a peca parada em um lugar que o servidor nao
        // tem — melhor devolve-la ao que esta gravado.
        this.$wire.moveElement(id, x, y, part).then(settled, settled);
    },

    /*
     * Deslocamento visual durante o arrasto. Arrastar a ponta de uma seta nao
     * tem previa: mover so um extremo exigiria redesenhar a linha no cliente,
     * e a posicao chega ao soltar.
     */
    offsetFor(id) {
        if (this.draggingId === id && this.draggingPart === null) {
            return `translate(${this.delta.x} ${this.delta.y})`;
        }

        const waiting = this.settling[id];

        if (waiting !== undefined && waiting.part === null) {
            return `translate(${waiting.x} ${waiting.y})`;
        }

        return '';
    },
});

/*
 * Atalhos de teclado do editor.
 *
 * Vive junto do arrasto e pelo mesmo motivo: e interacao local, com uma unica
 * chamada ao Livewire por atalho. As acoes sao as mesmas dos botoes — nenhum
 * atalho faz algo que a interface nao ofereca de outra forma.
 *
 * O passo do movimento e dado em unidades de campo: 10 equivalem a 1 metro, e
 * o Shift refina para 10 cm, que e o ajuste fino de quem esta encaixando uma
 * peca em cima da linha.
 */
window.tactiboardEditorShortcuts = () => ({
    STEP: 10,
    FINE_STEP: 1,

    /*
     * Um atalho nao pode disparar enquanto o usuario digita: "d" dentro do
     * campo de texto de uma observacao duplicaria o elemento em vez de
     * escrever a letra.
     *
     * O modal e verificado pela classe que ele mesmo poe no <body> enquanto
     * esta aberto. E acoplamento com o componente `x-modal`, mas e o unico
     * sinal que ele publica — e sem isso um Delete atras da confirmacao
     * apagaria um elemento que o usuario nao esta vendo.
     */
    shortcutsBlocked(event) {
        if (document.body.classList.contains('overflow-y-hidden')) {
            return true;
        }

        const target = event.target;

        if (! target) {
            return false;
        }

        return target.isContentEditable
            || ['INPUT', 'SELECT', 'TEXTAREA'].includes(target.tagName);
    },

    onShortcut(event) {
        if (this.shortcutsBlocked(event)) {
            return;
        }

        const withModifier = event.ctrlKey || event.metaKey;

        // Salvar independe de selecao: e a acao da prancheta inteira.
        if (withModifier && event.key.toLowerCase() === 's') {
            event.preventDefault();
            this.$wire.save();

            return;
        }

        const selectedId = this.$wire.selectedId;

        if (! selectedId) {
            return;
        }

        if (event.key === 'Escape') {
            this.$wire.select(null);

            return;
        }

        if (withModifier && event.key.toLowerCase() === 'd') {
            event.preventDefault();
            this.$wire.duplicateElement(selectedId);

            return;
        }

        if (['Delete', 'Backspace'].includes(event.key)) {
            // O Backspace volta uma pagina no historico em parte dos
            // navegadores. Com um elemento selecionado ele remove, e so.
            event.preventDefault();
            this.$wire.removeElement(selectedId);

            return;
        }

        const directions = {
            ArrowUp: [0, -1],
            ArrowDown: [0, 1],
            ArrowLeft: [-1, 0],
            ArrowRight: [1, 0],
        };

        if (event.key in directions) {
            // Sem isso a seta rola a pagina junto, e o campo sai da tela
            // enquanto o usuario posiciona a peca.
            event.preventDefault();

            const step = event.shiftKey ? this.FINE_STEP : this.STEP;
            const [dx, dy] = directions[event.key];

            this.$wire.moveElement(selectedId, dx * step, dy * step);
        }
    },
});
