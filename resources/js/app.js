import './bootstrap';

/*
 * O Alpine nao e importado aqui: quem o fornece e o Livewire, que embute a
 * propria copia e a inicializa. Manter o import manual do pacote `alpinejs`
 * faria duas instancias correrem na mesma pagina ("Detected multiple instances
 * of Alpine running") e quebraria o dropdown da navbar e os modais de exclusao.
 *
 * Decisao registrada na Fase 3. Ver docs/04 secao 8.2.
 */
