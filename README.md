# TactiBoard

Plataforma web para criar, organizar e compartilhar análises táticas de futebol.

O elemento central é a **Prancheta Tática**: uma análise visual criada sobre um
campo de futebol (ex.: "Saída de bola 4-3-3", "Pressão alta", "Escanteio
ofensivo", "Exercício de treinamento").

> **Status:** MVP completo. As fases 0 a 5 estão concluídas — ambiente, autenticação,
> gerenciamento de pranchetas, editor tático, compartilhamento por link público e
> as melhorias de experiência do editor. A Fase 6 (qualidade e documentação) está
> em andamento. O roadmap completo está em
> [`docs/05-development-roadmap.md`](docs/05-development-roadmap.md).

---

## O que dá para fazer

- **Criar uma conta** e entrar. O login bloqueia após cinco tentativas erradas.
- **Organizar as análises** no dashboard, com nome, categoria e datas.
- **Montar a jogada** no editor: campo oficial, jogadores dos dois lados, bola,
  cone, texto e seta — arrastando com o mouse ou com o dedo.
- **Ajustar pelo teclado**, com atalhos para salvar, duplicar, remover e mover.
- **Compartilhar por link público**, que qualquer pessoa abre sem ter conta e
  ninguém consegue editar. O endereço pode ser revogado ou trocado por um novo.

A descrição completa de cada funcionalidade, com limites e limitações conhecidas,
está em [`docs/08-features.md`](docs/08-features.md).

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

A aplicação fica disponível em <http://localhost:8080>. Crie uma conta em
`/register` e o dashboard abre em seguida.

> **Na primeira subida, o MySQL leva alguns segundos para inicializar.** Se o
> `migrate` responder `SQLSTATE[HY000] [2002] Connection refused`, aguarde e
> repita o comando — o container ainda estava criando o banco.

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
./vendor/bin/sail pest --coverage  # suíte com cobertura
./vendor/bin/sail bin pint         # formatação PSR
```

Criar o alias `alias sail='./vendor/bin/sail'` evita repetir o caminho.

---

## Testes

```bash
./vendor/bin/sail pest
```

A suíte usa Pest e cobre fluxos de usuário, permissões e as regras do canvas.
Toda funcionalidade tem teste de sucesso, de erro e de permissão — a regra está
em [`docs/06-coding-guidelines.md`](docs/06-coding-guidelines.md) §14, e a
organização entre feature e unit tests, em §13.

---

## Antes de publicar em um servidor

O `.env.example` descreve um ambiente **de desenvolvimento**. Em um servidor de
verdade, revise pelo menos:

- `APP_DEBUG=false` e `APP_ENV=production` — com o debug ligado, uma página de
  erro exibe o conteúdo do `.env`.
- `APP_KEY` gerada para aquele ambiente, e `APP_URL` com o domínio real.
- Senha de banco própria, diferente da padrão do Sail.
- `MAIL_MAILER` de verdade: com o padrão `log`, o e-mail de recuperação de senha
  é apenas gravado em `storage/logs`.
- `APP_BIND` e as portas publicadas — ver
  [`docs/04-technical-architecture.md`](docs/04-technical-architecture.md) §18.1,
  que explica por que o Docker contorna o firewall do host.

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
| [`08-features.md`](docs/08-features.md) | O que o produto faz hoje, funcionalidade por funcionalidade |
