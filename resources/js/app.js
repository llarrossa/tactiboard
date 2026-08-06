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

        this.$wire.moveElement(id, x, y, part);
    },

    /*
     * Deslocamento visual durante o arrasto. Arrastar a ponta de uma seta nao
     * tem previa: mover so um extremo exigiria redesenhar a linha no cliente,
     * e a posicao chega ao soltar.
     */
    offsetFor(id) {
        if (this.draggingId !== id || this.draggingPart !== null) {
            return '';
        }

        return `translate(${this.delta.x} ${this.delta.y})`;
    },
});
