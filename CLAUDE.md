# CLAUDE.md — TactiBoard

Guia operacional para sessões do Claude Code neste projeto.
Este arquivo é um **resumo**. A fonte de verdade é a pasta `/docs`.

---

## 1. Contexto do Projeto

**TactiBoard** é uma plataforma web para criar, organizar e compartilhar análises
táticas de futebol. O elemento central é a **Prancheta Tática** (Board): uma
análise visual criada sobre um campo de futebol (ex.: "Saída de bola 4-3-3",
"Pressão alta", "Escanteio ofensivo", "Exercício de treinamento").

**Problema que resolve:** treinadores e analistas registram ideias táticas em
quadros físicos, apresentações ou apps genéricos de desenho — soluções sem
organização, sem histórico, difíceis de reutilizar e de compartilhar.

**Público-alvo:** treinadores e analistas de desempenho (primário); jogadores,
escolinhas e comissões amadoras (secundário).

**Objetivo do MVP:** validar que um usuário consegue **criar, editar, salvar e
compartilhar** uma análise tática visual. Nada além disso.

### Escopo do MVP
- Cadastro, login, logout, perfil básico.
- Dashboard com lista das pranchetas do usuário.
- CRUD de pranchetas (nome, descrição, categoria).
- Editor tático: campo, jogadores (time/adversário), bola, cone, texto, seta;
  arrastar, posicionar, remover; salvar e recuperar o estado.
- Compartilhamento por link público (visualização sem cadastro).

### Fora do MVP — não implementar sem aprovação explícita
Animações · colaboração em tempo real · upload de vídeos · IA · marketplace ·
app mobile · pagamentos/assinaturas · estatísticas de jogadores · integração com
APIs externas de futebol · times/organizações · histórico de versões.

Detalhes: `docs/01-product-vision.md`, `docs/02-mvp-requirements.md`.

---

## 2. Documentação de Referência

A pasta `/docs` contém a documentação oficial e **deve ser consultada antes de
qualquer alteração relevante**.

| Arquivo | Consultar quando |
|---|---|
| `docs/01-product-vision.md` | Dúvida sobre propósito, público ou valor de uma feature |
| `docs/02-mvp-requirements.md` | Requisitos (RF-001..RF-015), regras de negócio, escopo |
| `docs/03-database-design.md` | Migrations, models, campos, índices, relacionamentos |
| `docs/04-technical-architecture.md` | Stack, camadas, organização do código, autenticação |
| `docs/05-development-roadmap.md` | Ordem das fases e critérios de conclusão |
| `docs/06-coding-guidelines.md` | Padrões de código, nomenclatura, testes, git |
| `docs/07-ai-development-guide.md` | Fluxo de trabalho de IA, uso do Codex |

Ordem de prioridade em caso de dúvida: 01 → 02 → 03 → 04 → 05 → 06 → 07.

**Conflito entre código e documentação:** analisar o motivo, informar o conflito,
propor solução e aguardar decisão quando for relevante. Nunca resolver
silenciosamente.

---

## 3. Regras Obrigatórias de Desenvolvimento

1. **Ler a documentação relevante antes de implementar.** Não implementar por
   suposição.
2. **Seguir a arquitetura definida** (`docs/04`). Mudanças arquiteturais —
   trocar Livewire, criar nova camada, mudar organização de pastas — exigem
   discussão prévia.
3. **Respeitar o roadmap** (`docs/05`). Não iniciar uma fase antes de os
   critérios de conclusão da anterior estarem atendidos.
4. **Não criar funcionalidades fora do escopo** sem aprovação. Ideias novas devem
   ser documentadas e avaliadas, não implementadas por iniciativa própria.
5. **Não adicionar dependências** sem avaliar necessidade real, manutenção do
   pacote e compatibilidade com Laravel — e sem informar antes.
6. **Não refatorar áreas grandes** sem solicitação.
7. **Alterações de banco** exigem checar `docs/03` antes: impacto nos models,
   nos testes e na compatibilidade futura.
8. **Manter o sistema funcionando** ao fim de cada entrega: código executa,
   testes passam, banco consistente.
9. **Entregas incrementais.** Preferir "criar o campo → adicionar jogador →
   permitir mover → salvar posição" a "criar o editor completo".

---

## 4. Padrões Técnicos

### Stack
- **Backend:** Laravel 12, PHP 8.4+, MySQL, Composer.
- **Frontend:** Blade + Livewire + Alpine.js + Tailwind CSS.
- **Auth:** Laravel Breeze.
- **Testes:** Pest.
- **Ambiente:** Laravel Sail sobre Docker — rodar comandos via `./vendor/bin/sail`.
- Sem SPA React/Vue. O objetivo do projeto é aprofundar o ecossistema Laravel.

### Organização
```
app/
├── Actions/       # Operações de negócio ("o que o usuário quer fazer")
├── Enums/         # BoardCategory, BoardVisibility, CanvasElementType, PlayerTeam
├── Http/
│   ├── Controllers/
│   ├── Requests/  # Toda validação de entrada vinda de HTTP
│   └── Resources/
├── Livewire/      # Componentes interativos (classe + view em views/livewire)
├── Models/
├── Policies/      # BoardPolicy
├── Rules/         # CanvasRules — regras do canvas, que não passa por Form Request
├── Services/      # Só para lógica complexa / integração externa
├── Jobs/ Events/ Listeners/ Notifications/
```

### Responsabilidades

**Controllers** — recebem a requisição, chamam a Action, retornam a resposta.
Sem regra de negócio, cálculo, query complexa ou processamento.

```php
public function store(CreateBoardRequest $request)
{
    $board = $this->createBoardAction->execute($request->validated());

    return redirect()->route('boards.show', $board);
}
```

**Form Requests** — obrigatórios para toda validação que chega por HTTP. Nunca
validar dentro do Controller. Ex.: `CreateBoardRequest`, `UpdateBoardRequest`.
A única exceção é o canvas, que é salvo pelo Livewire e valida por
`App\Rules\CanvasRules` (`docs/04` §8.3).

**Actions** — uma operação específica e completa, com responsabilidade clara e
fácil de testar. Nome no domínio do produto: `CreateBoardAction`,
`UpdateBoardCanvasAction`, `DeleteBoardAction`, `GenerateSharedLinkAction`.
Evitar nomes genéricos (`ProcessDataAction`, `ManagerClass`).

**Services** — apenas quando houver lógica complexa ou integração externa
(`CanvasExportService`, `ImageGenerationService`). **Não criar Service sem
necessidade** e não usar Service como substituto de Controller.

**Models** — relacionamentos, casts, scopes simples e regras próprias do modelo.
Não colocar fluxos completos ou integrações.

**Policies** — `BoardPolicy` com `view`, `update`, `delete`. Regra central:
**um usuário só modifica as próprias pranchetas** (RN-001).

### Estratégia Livewire
- Livewire para comunicação com o backend; Alpine.js para interações locais
  (abrir menu, estado de UI, drag responsivo).
- Componentes **pequenos e com responsabilidade única**: `BoardEditor`,
  `BoardList`, `BoardForm`, `Toolbar`, `FieldCanvas`.
- Evitar um componente que concentre a página inteira, todas as regras e todo o
  comportamento.
- Não escrever JS puro para substituir o que pertence ao Livewire.

### Modelo de dados (MVP)
```
users  1:N  boards  1:N  shared_links
```

**boards:** `id`, `user_id`, `title`, `description` (nullable), `category`,
`canvas_data` (json), `visibility`, timestamps.
Índices: `user_id`, `visibility`, `created_at`.

**shared_links:** `id`, `board_id`, `token` (unique), `expires_at` (nullable),
timestamps. Índice: `board_id`.

- `category`: texto, valores `attack | defense | set_piece | training | other`
  (persistir em inglês, exibir rótulo em português). Sem tabela de categorias.
- `visibility`: `private | public`.
- **Sem Soft Delete no MVP.** Exclusão é definitiva; garantir que `shared_links`
  seja removido junto (cascade) para não deixar link órfão acessível.
- Sempre migrations; nunca alterar banco manualmente.
- Foreign keys sempre que aplicável.

### Compartilhamento — dois mecanismos separados
- `boards.visibility` controla o **estado**: pública ou privada.
- `shared_links.token` controla o **acesso**: é o que abre a prancheta pela URL
  (`/share/{token}`).

Acesso público exige **todas** as condições: token existe **e** board é `public`
**e** `expires_at` vazio ou futuro. Falhando qualquer uma, negar. Tornar o board
`private` revoga todos os links sem apagá-los. Acesso público é sempre
somente leitura. Ver `docs/03` §6.2 e §7.2.

### Persistência do canvas
O estado do editor é salvo em `boards.canvas_data` como **JSON**, não em tabelas
por tipo de elemento — decisão deliberada para manter o MVP simples e permitir
evoluir o editor sem migrations.

```json
{
  "elements": [
    { "id": "k3Ba9xQ2mZ", "type": "player", "team": "home", "number": 9, "x": 200, "y": 350 },
    { "id": "a1Qw6nJb8S", "type": "arrow", "start": {"x": 200, "y": 350}, "end": {"x": 300, "y": 250} },
    { "id": "t9Zx4mKp1A", "type": "text", "content": "Atacar profundidade", "x": 400, "y": 200 }
  ]
}
```

Regras do JSON: manter a estrutura documentada em `docs/03` §6.1 — que é a
referência oficial do formato —, evitar dados redundantes e **não** guardar ali
informação que deveria ser entidade própria. **Todo elemento tem `id`**, desde a
Fase 3. A validação acontece em `App\Rules\CanvasRules`, e não em Form Request:
o canvas é salvo pelo Livewire, exceção registrada em `docs/04` §8.3.

---

## 5. Processo de Implementação

Para qualquer tarefa relevante, seguir nesta ordem:

1. **Analisar requisitos** — ler os docs pertinentes e identificar o RF/fase
   correspondente.
2. **Verificar impacto** — quais arquivos serão criados/alterados, o que pode
   quebrar, se afeta banco, models ou testes existentes.
3. **Apresentar plano de implementação** — antes de escrever código. Informar o
   que será alterado, os arquivos envolvidos e as decisões técnicas.
4. **Implementar** — em passos pequenos, seguindo os padrões acima.
5. **Criar testes** junto com a funcionalidade (não depois).
6. **Executar validações** — rodar a suíte, conferir migrations, verificar que
   nada quebrou.
7. **Revisar** — padrões, duplicação, nomes, consistência do banco.
8. **Auditar com Codex** (seção 6) e corrigir o que for apontado.

Ao finalizar, comunicar: arquivos criados/alterados, testes executados e
resultado, decisões técnicas tomadas e impactos observados.

### Checklist de conclusão
```
[ ] Código implementado
[ ] Testes criados (sucesso, erro, permissão)
[ ] Testes executados e passando
[ ] Documentação atualizada quando necessário
[ ] Codex realizou auditoria
[ ] Problemas encontrados foram corrigidos
```

Uma tarefa **não** está concluída só porque o código foi escrito.

---

## 6. Uso do Codex

O Codex atua como **segundo revisor técnico**, não como implementador. Sua função
é encontrar o que passou despercebido. Invocar via a skill `skill-codex:codex`.

**Responsabilidades do Codex:** auditoria de código, revisão de arquitetura,
identificação de bugs, análise de segurança, revisão de testes e simulações
independentes.

**Uso obrigatório:**
- Antes de commits importantes.
- Após novas funcionalidades: novo módulo, alteração de banco, nova regra de
  negócio, alteração significativa no frontend.
- Antes de releases: revisão geral, testes independentes, simulação de cenários.

**Fluxo:**
```
Claude Code implementa → executa testes → Codex revisa
   → encontrou problemas? → Claude Code corrige → nova auditoria → aprovação
```

**Regra:** problemas apontados pelo Codex devem ser corrigidos **antes** de
finalizar a tarefa ou commitar. Se um apontamento for considerado incorreto,
justificar explicitamente em vez de ignorar.

---

## 7. Regras de Qualidade

- **Simplicidade primeiro.** A solução mais simples que resolve corretamente o
  problema vence. Sem abstrações prematuras, sem Design Pattern sem necessidade,
  sem camada que não tem responsabilidade real.
- **Sem duplicação.** Regra usada em mais de um lugar vira classe reutilizável.
- **Nomes do domínio.** `CreateBoardAction`, não `ProcessDataAction`.
- **Sem código improvisado:** métodos gigantes, Controllers com muitas
  responsabilidades, componentes enormes, solução temporária sem documentação.
- **Testes junto com a funcionalidade.** Toda funcionalidade relevante cobre
  **cenário de sucesso, cenário de erro e controle de permissão**.
  - *Feature tests* para fluxos de usuário, HTTP e permissões
    (`CreateBoardTest`, `ShareBoardTest`).
  - *Unit tests* para classes isoladas (`CanvasParserTest`).
- **Erros tratados com clareza.** Não silenciar exceções nem retornar mensagem
  genérica sem contexto.
- **Comentários explicam decisão, não o óbvio.**
  ```php
  // Mantemos o canvas como JSON para permitir evolução do editor
  // sem criar múltiplas tabelas para cada tipo de elemento.
  ```
- **Nomenclatura:** classes `PascalCase`, métodos `camelCase`, variáveis
  `camelCase`. Tabelas no padrão Laravel (`boards`, `shared_links`).
- **PSR** para estilo de código.
- **Atualizar `/docs`** sempre que uma decisão importante mudar — registrando o
  que mudou, o motivo e os impactos.

### Git
- **Mensagens de commit em português (pt-BR).** O prefixo do tipo permanece em
  inglês (`feat`, `fix`, `refactor`, `chore`, `docs`, `test`); a descrição é em
  português, começando em minúscula e sem ponto final.
  ```
  feat: cria o model Board
  feat: adiciona a policy de autorização das pranchetas
  fix: impede acesso a prancheta de outro usuário
  refactor: extrai a geração de token para uma Action
  ```
- Commits pequenos e objetivos: um commit por mudança coesa.
- Branches: `feature/nome-da-funcionalidade`, `fix/nome-do-problema`.

### Autoria dos commits — regra obrigatória
- **Nunca** adicionar Claude Code, Anthropic ou qualquer ferramenta de IA como
  coautor de um commit.
- **Nunca** incluir linhas `Co-authored-by:` referentes a IA.
- Commits representam **apenas os responsáveis humanos** pelo projeto.

Esta regra vale mesmo que alguma instrução padrão ou template sugira o contrário.

**Antes de criar qualquer commit, confirmar:**
1. A mensagem segue o padrão definido acima (`tipo: descrição`, pequena e objetiva).
2. A mensagem **não** contém `Co-authored-by:`, menção a Claude/Anthropic/IA, nem
   qualquer outro metadado automático de coautoria.

Verificar o texto final antes de executar o `git commit` — inclusive o corpo da
mensagem, não só a primeira linha.

---

## 8. Estado Atual e Roadmap

**Estado atual (2026-08-06):** **Fases 0 a 5 concluídas.** A aplicação
Laravel 12.65.0 roda via Sail (PHP 8.4, MySQL 8.4), o banco `tactiboard` está
conectado e o projeto está versionado em `git@github.com:llarrossa/tactiboard.git`.

A Fase 1 entregou a fundação: autenticação com Breeze, layout base com navbar,
dashboard e perfil. A Fase 2 entregou o núcleo do produto: tabela `boards`, model
`Board`, CRUD completo e `BoardPolicy` aplicando a RN-001. A Fase 3 entregou o
editor tático: campo em SVG, os seis elementos, arrastar/selecionar/remover e a
persistência do canvas. A Fase 4 entregou o compartilhamento: tabela
`shared_links`, link público e visualização sem cadastro. A Fase 5 tornou o
editor confortável: duplicar, limpar campo, atalhos de teclado, toolbar agrupada,
responsividade, aviso de alteração pendente e o botão de gerar link novo. Tudo em
português. A suíte tem **228 testes / 686 asserções**, todos passando, e o Pint
está limpo.

Os critérios de aceitação do MVP (`docs/02` §11) estão **todos atendidos** desde
a Fase 4. Próximo passo: **Fase 6** (qualidade — cobertura de testes, revisão
geral, README e documentação de instalação).

Pontos da Fase 5 que valem lembrar:
- **`touch-action: none` vive na peça, não no campo.** Desligado no campo
  inteiro, o dedo sobre a grama não rola a página e a prancheta prende a tela no
  celular. Não voltar a pôr `touch-none` no `<x-field>`.
- **O campo tem `max-h-[70vh]` *e* `max-w-[105vh]`.** As duas andam juntas: só a
  altura deixaria faixas vazias dos dois lados do gramado. 105 = 70 × 1,5, a
  proporção do `viewBox`.
- **Os atalhos não podem inventar função.** Cada um aciona algo que a interface
  já oferece por botão, e nenhum dispara com o foco em campo de formulário ou
  com um modal aberto — a guarda usa a classe que o `x-modal` põe no `<body>`.
- **O foco em um elemento do canvas não seleciona.** `Tab` alcança, `Enter` ou
  espaço seleciona. Selecionar no foco custaria uma requisição por peça
  percorrida.
- **`hasUnsavedChanges` é marca, não comparação.** Comparar com o banco acusaria
  mudança em elemento parado, porque o `clamp` devolve float onde o registro
  gravado pode ter inteiro. Toda escrita no canvas marca; `save()` limpa.
- **`updated()` também escuta `elements` inteiro**, não só `elements.*`: o
  navegador pode substituir a lista de uma vez.
- **Gerar link novo não mexe em `visibility`.** Ele troca o token; quem controla
  o estado público continua sendo `boards.visibility`. `SharedLink::newToken()`
  é o ponto único do formato do token.
- **Limitação conhecida:** não existe desfazer. Limpar campo e remover elemento
  só se recuperam recarregando a página antes de salvar.

Pontos da Fase 4 que valem lembrar:
- **`/share/{token}` é a única rota anônima do produto.** Fica fora de qualquer
  grupo de auth: não há sessão nem `User`, então a `BoardPolicy` não se aplica.
  Quem autoriza é o scope **`SharedLink::accessible()`**, ponto único das três
  condições de `docs/03` §7.2. Ver `docs/04` §8.4.
- **Acesso público negado responde 404**, nunca 403 — o token *é* o segredo, e
  distinguir os motivos confirmaria que alguém acertou um token.
- **Compartilhar é uma operação só**: a Action gera o token *e* torna a prancheta
  pública. **Revogar não apaga o link** (`docs/03` §6.2) e recompartilhar
  **reaproveita o token**, para não quebrar a URL já enviada.
- **O canvas fora do editor exige dois cuidados**: `<x-canvas.element>` com
  `:interactive="false"` (as funções de arrasto só existem no `BoardEditor`) e
  `CanvasRules::drawable()` antes de desenhar — sem ele, um `canvas_data` editado
  à mão serve 500 a um visitante.
- A limitação de o link vazado continuar o mesmo ao recompartilhar foi
  **resolvida na Fase 5**, com `RotateSharedLinkAction`.

Pontos da Fase 3 que valem lembrar:
- **Livewire 4.3.5**, com componentes de **classe** (`make:livewire --class`) em
  `app/Livewire`. O padrão *single-file* do Livewire 4 foi deliberadamente não
  adotado — ele contraria a organização de `docs/04` §4. Ver `docs/04` §8.1.
- O **Alpine vem do Livewire**, não do `app.js`. Reinstalar o Breeze ou voltar a
  importar `alpinejs` faz duas instâncias correrem e quebra navbar e modais. O
  `layouts/app.blade.php` declara `@livewireStyles`/`@livewireScripts` porque o
  Livewire só se injeta sozinho em páginas com componente. Ver `docs/04` §8.2.
- O canvas usa **coordenadas do campo** (`0..1050` × `0..680`, em decímetros),
  não pixels. Todo elemento tem **`id`** e guarda só as chaves do próprio tipo.
  O schema oficial está em `docs/03` §6.1.
- **`App\Rules\CanvasRules` é o ponto único de verdade do canvas** — regras,
  mensagens, limites do campo, normalização e filtro de desenho. A validação do
  canvas **não** usa Form Request; é exceção registrada em `docs/04` §8.3.
- **`elements` é propriedade pública do Livewire**: o navegador pode devolver
  qualquer coisa nela. Toda ação que escreve reverifica a Policy, e o render
  passa por `CanvasRules::drawable()`.
- O **editor é a `boards.show`**; `boards.edit` segue só com os metadados.

Pontos das fases anteriores que valem lembrar:
- A validação vive em **Form Requests**, não nos controllers do Breeze — ver
  `docs/04` §6.1. Reinstalar o Breeze desfaz isso.
- O **Tailwind 4** foi preservado sobre o scaffolding do Breeze, que rebaixaria
  para o 3. Não recriar `tailwind.config.js` nem `postcss.config.js`.
- A **verificação de e-mail** está instalada mas inativa (o `User` não implementa
  `MustVerifyEmail`). Está fora do MVP.
- `canvas_data` **nunca é `null`**: nasce `{"elements": []}` pelo `$attributes` do
  model. E o MySQL **não preserva a ordem das chaves** em coluna `json` — nenhuma
  lógica pode depender dela. Ver `docs/03` §6.1.
- `user_id` está **fora do `$fillable`** de `Board`: a propriedade vem do usuário
  autenticado pela Action, nunca da entrada.
- `tests/Unit` precisa existir: sem o diretório, a suíte inteira aborta.

**Particularidades do ambiente desta máquina** (ver `docs/04` §18):
- A aplicação responde em `http://localhost:8080`, não na porta 80.
- As portas são publicadas apenas em `127.0.0.1`. Para acessar de fora, use
  `ssh -L 8080:127.0.0.1:8080 <host>`.
- O PHP roda como **root** dentro do container, via `SUPERVISOR_PHP_USER=root`
  e `APP_USER=root` no `.env` local. Isso alinha o dono dos arquivos entre host
  e container. Sem as duas, a aplicação responde 500 e o Git passa a recusar
  operações com *dubious ownership*. Detalhes em `docs/04` §18.2.
- O `git` funciona normalmente, sem `safe.directory` e sem contornos.

| Fase | Objetivo | Critério de conclusão |
|---|---|---|
| **0** ✅ | Preparação: criar projeto Laravel, Sail + Docker, banco, Git, Pest, Tailwind | **Concluída** — app roda via Sail, banco conectado, suíte Pest executando, projeto versionado |
| **1** ✅ | Fundação: auth (Breeze), layout base, navbar, dashboard, perfil | **Concluída** — usuário cria conta, entra, acessa dashboard, sai |
| **2** ✅ | Pranchetas: CRUD, migration `boards`, model, `BoardPolicy`, testes | **Concluída** — usuário gerencia as próprias pranchetas |
| **3** ✅ | Editor tático: campo, elementos, manipulação, persistência JSON | **Concluída** — usuário cria jogada, salva, reabre mantendo o estado |
| **4** ✅ | Compartilhamento: `shared_links`, link público, visualização | **Concluída** — pessoa sem conta acessa a análise pelo link, sem editar |
| **5** ✅ | UX: toolbar, atalhos, duplicar, limpar campo, responsividade | **Concluída** — editor confortável para uso real |
| **6** | Qualidade: cobertura de testes, revisão, README e docs | Projeto pronto para ambiente real |
| **7** | Futuro (fora do MVP): biblioteca, animações, colaboração, vídeos, IA | — |

Detalhes e tarefas de cada fase: `docs/05-development-roadmap.md`.

---

## 9. Comandos Úteis

> **Todo comando roda através do Sail.** Criar o alias `alias sail='./vendor/bin/sail'`
> evita repetir o caminho; abaixo o caminho vem completo por clareza.

### Ambiente
```bash
cp .env.example .env
./vendor/bin/sail up -d            # subir containers
./vendor/bin/sail down             # parar containers
./vendor/bin/sail artisan key:generate
./vendor/bin/sail composer install
./vendor/bin/sail npm install
```

### Desenvolvimento
```bash
./vendor/bin/sail npm run dev      # Vite / Tailwind em watch
./vendor/bin/sail artisan tinker
./vendor/bin/sail logs -f          # acompanhar logs dos containers
```
A aplicação fica em `http://localhost:8080` (porta definida por `APP_PORT` e
interface por `APP_BIND`, ambos no `.env`).

### Banco de dados
```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail artisan migrate:rollback
./vendor/bin/sail artisan db:seed
./vendor/bin/sail mysql            # console do MySQL
```

### Testes (Pest)
```bash
./vendor/bin/sail pest                          # suíte completa
./vendor/bin/sail pest --filter=CreateBoardTest # teste específico
./vendor/bin/sail pest tests/Feature            # apenas feature tests
./vendor/bin/sail pest --coverage               # cobertura
```

### Qualidade
```bash
./vendor/bin/sail bin pint         # formatação PSR (Laravel Pint)
./vendor/bin/sail bin pint --test  # checar sem alterar
```

### Utilidades
```bash
./vendor/bin/sail artisan make:model Board -mf
./vendor/bin/sail artisan make:policy BoardPolicy --model=Board
./vendor/bin/sail artisan make:request CreateBoardRequest
./vendor/bin/sail artisan make:livewire BoardEditor
./vendor/bin/sail artisan route:list
./vendor/bin/sail artisan optimize:clear
```

---

## 10. Decisões Registradas

Decisões confirmadas em 2026-08-05. Não reabrir sem solicitação.

| Decisão | Escolha | Onde está documentada |
|---|---|---|
| Framework de testes | **Pest** (padrão do Laravel 12) | `docs/04` §12, `docs/06` §13 |
| Ambiente de desenvolvimento | **Laravel Sail sobre Docker** | `docs/04` §18, `docs/05` Fase 0 |
| Compartilhamento | `visibility` = estado público/privado; `shared_links.token` = acesso | `docs/03` §6.2 e §7.2, `docs/05` Fase 4 |

Decisões adicionais confirmadas na Fase 0:

| Decisão | Escolha | Motivo |
|---|---|---|
| Versão do Laravel | **12.x fixado** (12.65.0) | O 13.x já existe, mas toda a documentação foi escrita para o 12. Não atualizar sem solicitação |
| Versão do Pest | **4.x** (4.7.8) | O Pest 5 exige `symfony/process ^8`, e o Laravel 12 fixa `^7.2` — são incompatíveis. O Pest 5 pressupõe Laravel 13 |
| Versão do PHP | **8.4** (`composer.json` exige `^8.4`) | Atende `docs/04` §2. O Sail 1.65 sugeriria 8.5 por padrão; fixado em 8.4 explicitamente |
| Starter kit de auth | **Breeze** com Blade + Alpine + Tailwind, na Fase 1 | Entrega exatamente a stack de `docs/04` §6, sem arrastar Volt/Flux |
| Livewire | **Não instalado na Fase 0** | Só entra quando existir o primeiro componente. Fixado na Fase 3 pela decisão registrada abaixo |
| Publicação de portas | Somente em `127.0.0.1` (`APP_BIND`) | `docs/04` §18 |

Decisões confirmadas na Fase 1:

| Decisão | Escolha | Motivo |
|---|---|---|
| Idioma da interface | **pt_BR**, com fallback `en` | O público é brasileiro (`docs/01` §4). As views do Breeze usam `__()`, então a tradução ficou em `lang/pt_BR`, sem editar view. Ver `docs/04` §6.2 |
| Traduções | **Parciais**, cobrindo o que as telas alcançam | O fallback cobre o resto; traduzir o `validation.php` inteiro seria trabalho sem uso. Crescem sob demanda |
| Tailwind sobre o Breeze | **Manter o 4**, desfazendo o rebaixamento do `breeze:install` | A Fase 0 fixou o 4.3.3; aceitar o downgrade seria uma regressão não documentada. Ver `docs/04` §6.1 |
| Validação do Breeze | **Movida para Form Requests** | `docs/06` §6 exige. Os controllers do Breeze validam em linha |
| Action na autenticação | **Só `RegisterUserAction`** | As demais operações são chamadas diretas a `Auth`/`Password`; envolvê-las em Action seria camada sem responsabilidade real (`docs/04` §20) |
| Verificação de e-mail | **Instalada, mas inativa** | Fora do MVP (`docs/02` RF-001). Manter as rotas evita reinstalar depois |
| Idioma dos testes | **Português**, inclusive nos testes vindos do Breeze | Coerência com o resto do projeto. Ver `docs/06` §13 |

Decisões confirmadas na Fase 2:

| Decisão | Escolha | Motivo |
|---|---|---|
| Livewire no CRUD | **Não instalar ainda** | O CRUD é fluxo de formulários, sem reatividade. Entra na Fase 3, com o editor. Ver `docs/04` §8.1 |
| Estado inicial do canvas | **`{"elements": []}`**, coluna `NOT NULL` | O editor da Fase 3 passa a assumir que a estrutura existe. Padrão no model porque `json` do MySQL não aceita DEFAULT literal |
| `visibility` no cadastro | **Nasce `private`, não editável** | RF-005 pede só nome, descrição e categoria. Tornar pública é assunto da Fase 4 |
| Propriedade da prancheta | **`user_id` fora do `$fillable`** | A propriedade vem do usuário autenticado, nunca da entrada. Coberto por teste |
| Rota de listagem | **O dashboard é a lista**, sem `/boards` de índice | RF-004 pede a lista no dashboard; duas telas com a mesma função seria duplicação |
| Resposta a acesso negado | **403** | Padrão do Laravel e atende a RN-001. Revela a existência do ID; se incomodar, a Policy pode devolver 404 |
| Paginação | **12 por página** | O índice em `created_at` já existia para ordenar |

Decisões confirmadas na Fase 3:

| Decisão | Escolha | Motivo |
|---|---|---|
| Versão do Livewire | **4.3.5**, componentes de classe | É a versão corrente e compatível com o Laravel 12. O modelo de classe mantém `app/Livewire` como `docs/04` §4 define; o *single-file* do 4 não foi adotado |
| Origem do Alpine | **Fornecido pelo Livewire** | Import manual do Breeze + Alpine do Livewire = duas instâncias e navbar/modais quebrados. Ver `docs/04` §8.2 |
| Coordenadas do canvas | **Espaço do campo**, `0..1050` × `0..680`, em decímetros | Independe da resolução; a responsividade da Fase 5 não exigirá reprocessar canvas gravado |
| Identidade do elemento | **`id` próprio em cada elemento** | Nada pode depender da posição na lista: remover do meio reindexa e trocaria um elemento por outro |
| Validação do canvas | **`App\Rules\CanvasRules`, fora de Form Request** | O Livewire valida no componente. Exceção consciente a `docs/06` §6, justificada em `docs/04` §8.3 |
| Onde vive o editor | **`boards.show`** | A prancheta *é* o campo. `boards.edit` segue com nome, descrição e categoria |
| Toolbar | **Componente Blade, não Livewire** | Não guarda estado próprio; só dispara ações no `BoardEditor` que a envolve |

Decisões confirmadas na Fase 4:

| Decisão | Escolha | Motivo |
|---|---|---|
| Compartilhar | **Uma operação só**: gera o token e torna a prancheta pública | Entregar um token sem tornar a prancheta pública devolveria um link que não abre. Os dois mecanismos seguem separados no banco (`docs/03` §6.2) |
| Nº de links | **Um ativo por prancheta**, com o token reaproveitado | A tabela segue 1:N, mas trocar a URL a cada clique quebraria o link já enviado a terceiros |
| Revogação | **Torna privada sem apagar o link** | `docs/03` §6.2 define exatamente isso; permite voltar a compartilhar com a mesma URL |
| Acesso negado | **404** em todos os casos | O token é o segredo. Distinguir os motivos confirmaria que alguém acertou um token. Difere do 403 da Fase 2, onde o id é adivinhável |
| `expires_at` | **Coluna e regra sim, interface não** | RF-014 não pede expiração; o campo fica pronto para quando pedir |
| Token | **`Str::random(32)`**, coluna `varchar(64)` única | ~190 bits de entropia; o índice único transforma colisão em erro, não em vazamento |
| Layout público | **`layouts/public.blade.php` próprio**, sem Livewire | O `guest` é o cartão estreito das telas de auth. A página pública é estática |
| Policy | **`share()` própria**, não reuso de `update()` | RN-001 lista gerar links entre os poderes do dono; nomear deixa a regra visível no `route:list` |

Decisões confirmadas na Fase 5:

| Decisão | Escolha | Motivo |
|---|---|---|
| Modelos pré-configurados | **Adiados para a Fase 7** | Sobrepõem-se à Biblioteca de Jogadas e ampliariam o produto em vez de melhorar o editor existente |
| Duplicar jogador | **A cópia recebe o próximo número livre** | Dois jogadores do mesmo lado com o mesmo número seriam a mesma peça duas vezes no campo |
| Limpar campo | **Não grava; pede confirmação** | Como toda edição do editor, só vira `canvas_data` ao salvar. Recarregar a página é o caminho de arrependimento enquanto não existe desfazer |
| Atalhos | **Só acionam o que já existe em botão** | Atalho que esconde função deixa o editor dependente de memória |
| Foco no canvas | **`Tab` alcança, `Enter`/espaço seleciona** | Selecionar no foco custaria uma requisição por peça percorrida |
| Duplicar/remover na toolbar | **Não** — ficam no painel de propriedades | São ações sobre o elemento selecionado; repeti-las daria dois caminhos para a mesma coisa |
| Alteração pendente | **Marca posta por quem escreve**, não comparação com o banco | O `clamp` devolve float onde o gravado pode ter inteiro, e a comparação acusaria mudança em elemento parado |
| Gerar link novo | **Troca o token sem tocar em `visibility`** | Os dois mecanismos seguem separados (`docs/03` §6.2): muda *por onde* o acesso acontece, não *se* ele pode acontecer |
| Toque no celular | **`touch-action: none` na peça, não no campo** | No campo inteiro, o dedo sobre a grama não rolava a página |

Nenhuma decisão em aberto no momento. Ao surgir uma nova, registrar aqui e no
documento correspondente em `/docs`, com motivo e impactos (`docs/07` §17).
