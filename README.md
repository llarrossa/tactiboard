# TactiBoard

Plataforma web para criar, organizar e compartilhar análises táticas de futebol.

O elemento central é a **Prancheta Tática**: uma análise visual criada sobre um
campo de futebol (ex.: "Saída de bola 4-3-3", "Pressão alta", "Escanteio
ofensivo", "Exercício de treinamento").

> **Status:** Fases 0, 1, 2 e 3 concluídas — ambiente preparado, autenticação no
> ar, gerenciamento de pranchetas (criar, listar, ver, editar, excluir) e o
> editor tático funcionando: campo, jogadores, bola, cone, texto e setas, com
> arrastar, remover e salvar. O compartilhamento por link entra na Fase 4.
> O roadmap completo está em [`docs/05-development-roadmap.md`](docs/05-development-roadmap.md).

---

## Stack

Laravel 12 · PHP 8.4 · MySQL 8.4 · Blade · Livewire 4 · Alpine.js · Tailwind
CSS 4 · Laravel Breeze · Pest 4 · Laravel Sail sobre Docker

O Alpine é fornecido pelo Livewire, não importado no `app.js` — as duas cópias
na mesma página se atrapalham. Ver [`docs/04`](docs/04-technical-architecture.md) §8.2.

---

## Requisitos

- Docker e Docker Compose

Não é necessário ter PHP, Composer, MySQL ou Node instalados no host — tudo roda
em containers gerenciados pelo Sail.

---

## Instalação

```bash
git clone git@github.com:llarrossa/tactiboard.git
cd tactiboard
cp .env.example .env
```

Instale as dependências PHP usando um container (o host não precisa de PHP):

```bash
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd)":/app -w /app \
  laravelsail/php84-composer:latest composer install
```

Suba o ambiente e prepare a aplicação:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

A aplicação fica disponível em <http://localhost:8080>.

> As portas 80 e 3306 costumam estar ocupadas, então o `.env.example` já usa
> `APP_PORT=8080` e `FORWARD_DB_PORT=33061`. As portas são publicadas apenas em
> `127.0.0.1` (`APP_BIND`); para acessar de outra máquina, use um túnel SSH:
> `ssh -L 8080:127.0.0.1:8080 <host>`.
>
> **Se você trabalha como root nesta máquina**, descomente `SUPERVISOR_PHP_USER`
> e `APP_USER` no `.env` antes de subir os containers. Sem isso a aplicação
> responde 500 e o Git recusa operações com *dubious ownership*. O porquê está
> em [`docs/04-technical-architecture.md`](docs/04-technical-architecture.md)
> §18.2.

---

## Comandos do dia a dia

```bash
./vendor/bin/sail up -d            # subir containers
./vendor/bin/sail down             # parar containers
./vendor/bin/sail npm run dev      # Vite em watch
./vendor/bin/sail artisan migrate  # migrations
./vendor/bin/sail pest             # suíte de testes
./vendor/bin/sail bin pint         # formatação PSR
```

Criar o alias `alias sail='./vendor/bin/sail'` evita repetir o caminho.

---

## Documentação

A pasta [`docs/`](docs/) é a fonte de verdade do projeto.

| Documento | Conteúdo |
|---|---|
| [`01-product-vision.md`](docs/01-product-vision.md) | Visão do produto, público-alvo, proposta de valor |
| [`02-mvp-requirements.md`](docs/02-mvp-requirements.md) | Requisitos funcionais e regras de negócio |
| [`03-database-design.md`](docs/03-database-design.md) | Modelagem do banco de dados |
| [`04-technical-architecture.md`](docs/04-technical-architecture.md) | Stack, camadas, organização do código |
| [`05-development-roadmap.md`](docs/05-development-roadmap.md) | Fases de desenvolvimento |
| [`06-coding-guidelines.md`](docs/06-coding-guidelines.md) | Padrões de código, testes, git |
| [`07-ai-development-guide.md`](docs/07-ai-development-guide.md) | Fluxo de trabalho com ferramentas de IA |
