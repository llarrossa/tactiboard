# TactiBoard — Development Roadmap

## 1. Objetivo do Documento

Este documento define o plano de desenvolvimento do TactiBoard.

O objetivo é organizar a implementação do produto em etapas pequenas e evolutivas, garantindo que cada fase entregue valor e construa uma base sólida para as próximas funcionalidades.

A implementação deve seguir esta ordem, evitando desenvolver funcionalidades avançadas antes da conclusão das fundações necessárias.

---

# 2. Estratégia de Desenvolvimento

O desenvolvimento será dividido em fases.

Cada fase deve:

- Ter objetivos claros.
- Possuir critérios de conclusão.
- Manter o sistema funcional.
- Possuir testes quando aplicável.
- Evitar quebrar funcionalidades existentes.

---

# 3. Fase 0 — Preparação do Projeto

**Status: concluída em 2026-08-05.**

## Objetivo

Criar a base inicial do ambiente de desenvolvimento.

---

## Tarefas

- [x] Criar projeto Laravel.
- [x] Configurar ambiente local com Laravel Sail e Docker.
- [x] Configurar banco de dados.
- [x] Configurar controle de versão.
- [x] Configurar variáveis de ambiente.
- [x] Instalar dependências iniciais.
- [x] Configurar Pest.
- [x] Configurar Tailwind CSS.

---

## Critérios de conclusão

- [x] Aplicação Laravel executando localmente através do Sail.
- [x] Banco conectado.
- [x] Estrutura inicial funcionando.
- [x] Suíte de testes executando com Pest.
- [x] Projeto versionado no Git.

---

## Resultado

| Item | Versão / estado |
|---|---|
| Laravel | 12.65.0 |
| PHP | 8.4.24 (container Sail) |
| Banco | MySQL 8.4, schema `tactiboard` conectado, 3 migrations aplicadas |
| Testes | Pest 4.7.8, suíte executando |
| Frontend | Tailwind CSS 4.3.3 via Vite 7, build gerado |
| Formatação | Laravel Pint sem violações |
| Git | Repositório em `main`, publicado em `git@github.com:llarrossa/tactiboard.git` |
| Aplicação | `http://localhost:8080` |

Particularidades de ambiente (portas, exposição de rede e uid do container)
estão documentadas em `04-technical-architecture.md` §18.1 e §18.2.

Não foi instalado Livewire nem Breeze nesta fase — ambos entram a partir da
Fase 1, conforme decisão registrada em `CLAUDE.md` §10.

---

# 4. Fase 1 — Fundação da Aplicação

**Status: concluída em 2026-08-05.**

## Objetivo

Criar a estrutura base utilizada por todo o sistema.

---

## Funcionalidades

### Autenticação

Implementar:

- [x] Cadastro.
- [x] Login.
- [x] Logout.
- [x] Recuperação de senha.

---

### Layout Base

Criar:

- [x] Navbar.
- [x] Área autenticada.
- [x] Dashboard inicial.
- [x] Componentes visuais básicos.

---

### Usuário

Implementar:

- [x] Perfil básico.
- [x] Dados do usuário.

---

## Critérios de conclusão

Usuário consegue:

- [x] Criar uma conta.
- [x] Entrar no sistema.
- [x] Acessar dashboard.
- [x] Encerrar sessão.

---

## Resultado

| Item | Estado |
|---|---|
| Autenticação | Laravel Breeze 2.4, stack Blade + Alpine + Tailwind |
| Rotas | `/`, `/dashboard`, `/profile`, além das rotas de auth em `routes/auth.php` |
| Camadas | Validação em Form Requests; `RegisterUserAction` para o cadastro |
| Idioma | Interface e mensagens em pt-BR (`APP_LOCALE=pt_BR`, `lang/pt_BR`) |
| Testes | 43 testes, 134 asserções, todos passando |
| Formatação | Laravel Pint sem violações |
| Banco | Sem migrations novas — `users`, `password_reset_tokens` e `sessions` já existiam |

Nenhuma migration foi criada nesta fase: a migration padrão do Laravel
(`0001_01_01_000000_create_users_table`) já cria as três tabelas que o Breeze usa.

### Ajustes feitos sobre o scaffolding do Breeze

O Breeze entrega código funcional, mas não alinhado a todas as regras deste
projeto. Foram feitos quatro ajustes, registrados aqui para que não sejam
desfeitos por engano em uma reinstalação:

1. **Tailwind 4 mantido.** O `breeze:install` rebaixa o projeto para o Tailwind 3
   (cria `tailwind.config.js` e `postcss.config.js`, troca o `app.css` pelas
   diretivas `@tailwind`). A Fase 0 fixou o Tailwind 4, então a configuração foi
   restaurada para o modelo CSS-first, com `@plugin '@tailwindcss/forms'`.
2. **Validação movida para Form Requests.** Os controllers do Breeze validam em
   linha; `docs/06` §6 exige Form Requests. Ver `04-technical-architecture.md` §6.1.
3. **Cadastro extraído para uma Action.** `RegisterUserAction` concentra a criação
   do usuário, seguindo o padrão de `docs/06` §7.
4. **Logo substituído.** O `x-application-logo` vinha com a marca do Laravel; foi
   trocado por um campo de futebol, a marca do produto.

### Fora do escopo desta fase

- **Verificação de e-mail:** o Breeze instala as rotas e telas, mas o recurso está
  **inativo** — o model `User` não implementa `MustVerifyEmail`. As rotas foram
  mantidas para não precisar reinstalá-las caso o recurso seja adotado. Não faz
  parte do MVP (`docs/02` RF-001).
- **Lista de pranchetas no dashboard:** RF-004 depende da entidade `Board`, que
  entra na Fase 2. O dashboard desta fase é apenas a casca autenticada.

---

# 5. Fase 2 — Módulo de Pranchetas

**Status: concluída em 2026-08-05.**

## Objetivo

Criar o núcleo inicial do produto.

---

## Funcionalidades

Implementar:

- [x] Criar prancheta.
- [x] Listar pranchetas.
- [x] Visualizar detalhes.
- [x] Editar informações.
- [x] Excluir prancheta.

---

## Banco

Criar:

- [x] Migration boards.
- [x] Model Board.
- [x] Relacionamento User → Boards.

---

## Segurança

Implementar:

- [x] BoardPolicy.
- [x] Controle de propriedade.

---

## Testes

Criar testes para:

- [x] Criação de prancheta.
- [x] Listagem.
- [x] Edição.
- [x] Exclusão.
- [x] Permissões.

---

## Critérios de conclusão

- [x] Usuário consegue gerenciar suas próprias pranchetas.

---

## Resultado

| Item | Estado |
|---|---|
| Banco | Tabela `boards` conforme `docs/03` §6, com os três índices de §9 |
| Model | `Board` com `user()`, casts de enum e do canvas |
| Enums | `BoardCategory` e `BoardVisibility`, persistidos em inglês e exibidos em português |
| Camadas | `CreateBoardAction`, `UpdateBoardAction`, `DeleteBoardAction`; `CreateBoardRequest` e `UpdateBoardRequest` |
| Autorização | `BoardPolicy` (view, update, delete), presa às rotas por `can` |
| Telas | Dashboard com a lista, criar, ver e editar/excluir |
| Testes | 79 testes, 228 asserções, todos passando |
| Formatação | Laravel Pint sem violações |

### Decisões desta fase

1. **CRUD sem Livewire.** O módulo é um CRUD de formulários, sem reatividade:
   controllers finos, Form Requests, Actions e Blade dão conta. Instalar o
   Livewire agora seria dependência sem necessidade (`CLAUDE.md` §3, regra 5).
   Ele entra na Fase 3, onde o editor realmente precisa. Ver `docs/04` §8.1.
2. **`canvas_data` nasce com `{"elements": []}`**, nunca `null`, via `$attributes`
   no model. Assim o editor da Fase 3 não precisa tratar ausência de estrutura.
   A coluna é `NOT NULL`.
3. **`visibility` nasce `private`** e não é editável nesta fase. O formulário de
   RF-005 tem apenas nome, descrição e categoria; tornar pública é assunto do
   compartilhamento, na Fase 4.
4. **`user_id` fora do `$fillable`.** A propriedade vem do usuário autenticado
   pela Action, nunca da entrada. Há teste que envia `user_id` de outra pessoa e
   confirma que é ignorado.
5. **Dashboard é a listagem.** RF-004 pede a lista no dashboard, então não existe
   uma rota `/boards` de índice — teria duas telas com a mesma função.
6. **Acesso negado responde 403**, não 404. É o padrão do Laravel e atende a
   RN-001. Vale saber que isso revela a existência do ID a quem tentar adivinhar;
   se algum dia isso incomodar, a Policy pode passar a devolver 404.
7. **Paginação de 12 por página** na listagem. O índice em `created_at` já existia
   para ordenar (`docs/03` §9).

### Aprendizado sobre o canvas em JSON

O MySQL **não preserva a ordem das chaves** em coluna `json`: o que volta tem o
mesmo conteúdo em outra ordem. Nenhuma lógica pode depender dessa ordem — vale
para o editor da Fase 3 e para qualquer comparação de canvas em teste, que deve
usar igualdade por conteúdo e não identidade estrita.

---

# 6. Fase 3 — Editor Tático MVP

**Status: concluída em 2026-08-05.**

## Objetivo

Construir a principal funcionalidade do produto.

---

## Funcionalidades

Criar editor visual com:

### Campo

- Campo completo.
- Linhas oficiais.
- Área visual de edição.

---

### Elementos

Adicionar:

- Jogadores.
- Adversários.
- Bola.
- Cone.
- Texto.
- Setas.

---

### Manipulação

Permitir:

- Arrastar elementos.
- Posicionar.
- Remover.
- Alterar propriedades básicas.

---

### Persistência

Salvar:

- Estado do canvas.
- Elementos.
- Posicionamentos.

Formato:

JSON.

---

## Critérios de conclusão

Usuário consegue:

- [x] Abrir uma prancheta.
- [x] Criar uma jogada visual.
- [x] Salvar.
- [x] Fechar.
- [x] Abrir novamente mantendo a configuração.

---

## Resultado

| Item | Estado |
|---|---|
| Livewire | 4.3.5, componentes de classe em `app/Livewire` (ver `docs/04` §8.1) |
| Campo | `<x-field>` em SVG, medidas oficiais da IFAB, coordenadas `0..1050` × `0..680` |
| Elementos | Jogador (dois lados), bola, cone, texto e seta, cada um com componente Blade próprio |
| Manipulação | Arrastar (Alpine), selecionar, remover e editar número, lado e texto |
| Camadas | `UpdateBoardCanvasAction`; regras centralizadas em `App\Rules\CanvasRules` |
| Enums | `CanvasElementType` e `PlayerTeam` |
| Autorização | `BoardEditor` autoriza `view` no `mount()` e `update` em toda escrita |
| Telas | O editor é a `boards.show`; `boards.edit` segue só com os metadados |
| Testes | 140 testes, 498 asserções, todos passando |
| Formatação | Laravel Pint sem violações |
| Banco | Sem migrations novas — `canvas_data` já existia desde a Fase 2 |

Nenhuma migration foi criada: a decisão da Fase 2 de guardar o canvas em JSON
entregou exatamente o que prometia — o editor inteiro coube sem tocar no schema.

### Decisões desta fase

1. **Livewire 4, não 3.** O 4.3.5 é a versão corrente e é compatível com o
   Laravel 12. Os componentes usam o modelo de classe, então a organização de
   `docs/04` §4 e `docs/06` §3 continua valendo. Ver `docs/04` §8.1.
2. **O Alpine passa a vir do Livewire.** O import manual do Breeze foi removido
   para não rodar duas instâncias. Ver `docs/04` §8.2.
3. **Coordenadas no espaço do campo**, não em pixels: `0..1050` × `0..680`, em
   decímetros. O JSON não depende da resolução, e a responsividade da Fase 5 não
   vai exigir reprocessar canvas gravado. Ver `docs/03` §6.1.
4. **Todo elemento tem `id`.** Nada pode depender da posição na lista: remover um
   elemento do meio reindexa o array e trocaria um elemento por outro na tela.
5. **Validação do canvas fora de Form Request**, centralizada em `CanvasRules`.
   É exceção consciente a `docs/06` §6, justificada em `docs/04` §8.3.
6. **O editor é a `boards.show`.** A prancheta *é* o campo; `boards.edit` segue
   com nome, descrição e categoria.
7. **Toolbar é componente Blade, não Livewire.** Ela não guarda estado próprio,
   só dispara ações no `BoardEditor` que a envolve.

### Aprendizados

- **`elements` é propriedade pública de componente Livewire**, então o navegador
  pode devolver qualquer coisa nela. Um elemento malformado derrubava o render
  inteiro; `CanvasRules::drawable()` filtra o que não é desenhável e a mensagem
  de validação explica o resto.
- **Prender cada ponta da seta em separado a deformava**: arrastá-la contra a
  linha lateral encolhia a jogada. Mover a seta inteira limita o deslocamento,
  não cada ponta.
- **Autorização de rota não basta para um componente Livewire.** A sessão pode
  mudar depois que o editor está aberto, então toda ação que escreve reverifica.

---

# 7. Fase 4 — Compartilhamento Público

**Status: concluída em 2026-08-06.**

## Objetivo

Permitir que análises sejam compartilhadas externamente.

---

## Funcionalidades

Implementar:

- [x] Criar link público.
- [x] Visualização pública.
- [x] Controle de acesso.

---

## Banco

Criar:

- [x] Migration shared_links.
- [x] Model SharedLink.

---

## Segurança

Garantir:

- [x] Visitante não consegue editar.
- [x] Apenas visualizar.
- [x] Prancheta privada nega acesso mesmo com token válido.
- [x] Token inválido nega acesso mesmo com prancheta pública.

---

## Critérios de conclusão

- [x] Uma pessoa sem conta consegue acessar uma análise através de um link.

---

## Resultado

| Item | Estado |
|---|---|
| Banco | Tabela `shared_links` conforme `docs/03` §7, com `board_id` em cascata e `token` único |
| Model | `SharedLink` com `board()`, cast de `expires_at` e o scope `accessible()` |
| Camadas | `GenerateSharedLinkAction` e `RevokeSharedLinkAction`; `SharedLinkController` (dono) e `SharedBoardController` (anônimo) |
| Autorização | `BoardPolicy::share()`, presa às rotas de escrita por `can`; a rota pública não usa Policy |
| Telas | Painel de compartilhamento em `boards.show`; `share/show` sobre o novo `layouts/public` |
| Testes | 169 testes, 562 asserções, todos passando |
| Formatação | Laravel Pint sem violações |

Com esta fase os critérios de aceitação do MVP (`docs/02` §11) estão todos
atendidos.

### Decisões desta fase

1. **Compartilhar é uma operação só.** A Action gera o token *e* define
   `visibility = public`. Os dois mecanismos continuam separados no banco e na
   regra de acesso (`docs/03` §6.2); o que se unifica é a operação de produto —
   entregar um token sem tornar a prancheta pública devolveria um link que não
   abre.
2. **Um link ativo por prancheta.** A tabela permanece 1:N como §8 define, mas a
   interface mantém um único link, e compartilhar de novo **reaproveita o token**.
   Trocar a URL a cada clique quebraria o link já enviado a outras pessoas.
3. **Revogar não apaga o link.** `docs/03` §6.2 define que tornar a prancheta
   privada revoga o acesso *sem remover* os links. Assim o dono volta a
   compartilhar depois com a mesma URL.
4. **Acesso público negado responde 404**, em todos os casos. Ver `docs/03` §7.2.
5. **`expires_at` sem interface.** A coluna e a regra são implementadas e
   testadas, mas nenhuma tela define expiração — RF-014 não pede. O campo fica
   pronto para quando pedir.
6. **Layout público próprio.** O `guest.blade.php` é o cartão estreito das telas
   de autenticação. Ver `docs/04` §8.4.

### Limitações conhecidas

1. ~~Como compartilhar reaproveita o token, **um link que vaze continua o mesmo
   ao recompartilhar**.~~ **Resolvido na Fase 5** (2026-08-06) por
   `RotateSharedLinkAction`: o dono gera um endereço novo e o anterior deixa de
   abrir na hora. Não entrou na Fase 4 porque RF-014 não pede.
2. **A serialização de dois compartilhamentos simultâneos não tem teste de
   regressão.** `GenerateSharedLinkAction` trava a linha da prancheta com
   `lockForUpdate()`, mas provar isso exigiria um teste com requisições
   concorrentes de verdade, que a suíte não sabe montar hoje. O comportamento
   sequencial está coberto.

### Por que `board_id` não é único

A auditoria sugeriu um índice único em `shared_links.board_id` para impor "um
link por prancheta" no banco. Foi recusado: §8 deste documento define o
relacionamento como **1:N** de propósito, e o índice único contradiria o schema
documentado, fechando a evolução que ele antecipa. O "um link ativo" é invariante
**da interface**, garantido pelo lock na Action.

Pelo mesmo raciocínio, não há *retry* em colisão de token: 32 caracteres dão
entropia suficiente para tornar a colisão desprezível, e o índice único faz a
gravação falhar fechada — que é o comportamento correto.

### Aprendizados

- **O canvas fora do editor precisa de dois cuidados** — desligar as ligações de
  arrasto e passar por `CanvasRules::drawable()`. Sem o segundo, um `canvas_data`
  editado à mão no banco serviria 500 a um visitante anônimo. Ver `docs/04` §8.4.
- **`x-model` deixa o campo vazio no HTML servido.** A URL do link estava só no
  estado do Alpine, então não existia antes de a página hidratar — e um teste que
  procurava a URL na resposta reprovou. Passou a vir no atributo `value`, com o
  Alpine lendo dela por `$refs`.
- **`navigator.clipboard` não existe em contexto inseguro.** Em HTTP fora de
  `localhost` o botão de copiar falharia em silêncio. Ele agora seleciona o texto
  sempre e só sinaliza "copiado" quando a cópia de fato aconteceu.

---

# 8. Fase 5 — Melhorias de Experiência

**Status: concluída em 2026-08-06.**

## Objetivo

Melhorar usabilidade do produto.

---

## Funcionalidades

- [x] Melhorar toolbar.
- [x] Atalhos de teclado.
- [x] Duplicar elementos.
- [x] Limpar campo.
- [x] Melhorar responsividade.
- [x] Aviso de alterações não salvas.
- [x] Gerar novo link público.
- [ ] ~~Modelos pré-configurados~~ — movidos para a Fase 7.

---

## Critério

- [x] O editor deve ser confortável para uso real.

---

## Resultado

| Item | Estado |
|---|---|
| Editor | Duplicar elemento, limpar campo, atalhos de teclado e aviso de alteração pendente |
| Acessibilidade | Elementos do campo alcançáveis por `Tab`, selecionáveis por `Enter`/espaço e anunciados com rótulo próprio |
| Toolbar | Grupos rotulados, contador `n/100` e botões desabilitados ao atingir o limite |
| Responsividade | Toque desligado na peça e não no campo; campo limitado a 70vh; painel de propriedades empilhado no celular |
| Compartilhamento | `RotateSharedLinkAction` e `boards.share.update`, com `SharedLink::newToken()` centralizando o formato |
| Camadas | Nenhuma camada nova; nenhuma dependência nova; nenhuma migration |
| Testes | 232 testes, 693 asserções, todos passando |
| Formatação | Laravel Pint sem violações |

### Decisões desta fase

1. **Modelos pré-configurados saem da fase.** Eles se sobrepõem à Biblioteca de
   Jogadas da Fase 7 (salvar modelos, reutilizar análises) e ampliariam o
   produto em vez de melhorar o editor existente. Decisão do responsável pelo
   projeto, tomada na abertura da fase.
2. **Duplicar dá um número novo ao jogador.** Dois jogadores do mesmo lado com o
   mesmo número seriam a mesma peça duas vezes no campo. A cópia também nasce
   deslocada, e junto da borda o deslocamento inverte — preso pelo limite do
   campo, ela pararia exatamente sobre o original.
3. **Limpar campo não grava.** Como toda edição do editor, só vira `canvas_data`
   ao salvar. Recarregar a página traz a jogada de volta, que é o caminho de
   arrependimento enquanto não existe desfazer.
4. **Atalhos não inventam função.** Cada um deles aciona algo que a interface já
   oferece por botão. Eles não disparam com o foco em campo de formulário nem
   com um modal aberto.
5. **O foco não seleciona.** Percorrer o campo com `Tab` custaria uma ida ao
   servidor por peça; a seleção é explícita, por `Enter` ou espaço.
6. **Duplicar e remover ficam só no painel de propriedades.** São ações sobre o
   elemento selecionado; repeti-las na toolbar daria dois caminhos para a mesma
   coisa sem informação nova.
7. **A marca de alteração pendente é posta por quem escreve**, e não calculada
   comparando com o banco: o `clamp` devolve float onde o registro gravado pode
   ter inteiro, e a comparação acusaria mudança em elemento parado.
8. **Gerar novo link não mexe na visibilidade.** Os dois mecanismos seguem
   separados (`docs/03` §6.2): a operação muda *por onde* o acesso acontece, não
   *se* ele pode acontecer.

### Aprendizados

- **`touch-action` pertence à peça, não ao campo.** Desligado no campo inteiro,
  como estava desde a Fase 3, o dedo sobre a grama não rolava a página — no
  celular a prancheta prendia a tela. Na peça, arrastar move e a grama rola.
- **Limitar só a altura do SVG deixa faixas vazias.** O elemento mantinha a
  largura inteira quando a altura era limitada. A largura máxima precisa
  acompanhar a altura na proporção do `viewBox`.
- **`updated()` não vê só os caminhos aninhados.** O navegador pode substituir
  `elements` inteiro de uma vez, e essa também é uma edição por gravar. A
  condição que olhava apenas `elements.` deixava passar exatamente o caso em que
  o trabalho corre risco.
- **Comparar posições exige normalizar o tipo.** `1050 === 1050.0` é falso em
  PHP, e a comparação estrita dizia que um elemento parado tinha se movido.

### Achados da auditoria com Codex

A auditoria (gpt-5.4, esforço alto) apontou cinco itens. Quatro foram corrigidos
antes do fechamento da fase:

1. `RotateSharedLinkAction` criava um link quando não havia nenhum — passou a
   exigir link existente e responder 404.
2. `duplicateElement()` autorizava indiretamente, pelo `push()`: um id
   inexistente devolvia 200 a quem tinha perdido o acesso. A verificação subiu
   para o topo do método.
3. `updated()` usava `validateOnly` também na escrita da lista inteira, que só
   alcança as regras da raiz. Agora valida o canvas inteiro nesse caminho.
4. A marca de alteração pendente era ligada mesmo por operação que não muda
   nada. Arrastar uma peça já presa na borda deixou de marcar.

O quinto apontamento — de que o *fallback* do botão de copiar link quebraria em
contexto sem `navigator.clipboard` — **foi recusado**. O *optional chaining*
interrompe a cadeia inteira, incluindo o `.then()` seguinte, quando o operando é
nulo; o comportamento foi confirmado no navegador. O código está correto.

### Limitações conhecidas

1. **Não existe desfazer.** Limpar campo e remover elemento se recuperam
   recarregando a página antes de salvar, e nada mais. Um histórico de edição é
   assunto de fase futura.
2. **Os atalhos não têm teste automatizado.** Eles vivem no Alpine, e a suíte
   cobre o que a página entrega (as ligações, o foco e os rótulos) mais os
   métodos que eles chamam. O comportamento de ponta a ponta foi verificado no
   navegador.
3. **A marca de alteração pendente não volta atrás.** Se o canvas retornar ao
   estado gravado por outro caminho — adicionar uma peça e depois limpar o campo
   —, o aviso continua ligado. É deliberado: avisar à toa custa uma confirmação,
   e deixar de avisar custa o trabalho do usuário.
4. **Mover pelo teclado custa uma requisição por tecla.** Aceitável no uso real,
   já que o Livewire agrupa chamadas próximas; se incomodar, o acúmulo local com
   envio ao soltar a tecla resolve.

---

# 9. Fase 6 — Qualidade e Profissionalização

## Objetivo

Preparar o projeto para ambiente real.

---

## Implementar

### Testes

Aumentar cobertura:

- Feature tests.
- Unit tests.

---

### Código

Revisar:

- Organização.
- Duplicações.
- Performance.
- Padrões.

---

### Documentação

Criar:

- README.
- Guia de instalação.
- Documentação de funcionalidades.

---

# 10. Fase 7 — Funcionalidades Futuras

Estas funcionalidades ficam fora do MVP inicial.

---

## Biblioteca de Jogadas

Permitir:

- Salvar modelos.
- Categorizar estratégias.
- Reutilizar análises.

---

## Animações

Permitir:

- Sequência de movimentos.
- Reprodução da jogada.

---

## Colaboração

Permitir:

- Equipes.
- Múltiplos usuários.
- Edição simultânea.

---

## Vídeos

Permitir:

- Upload.
- Associação com análises.

---

## Inteligência Artificial

Possibilidades:

- Criar sugestões táticas.
- Gerar exercícios.
- Analisar padrões.

---

# 11. Regras para Implementação

## Não pular fases

Uma fase só deve começar quando os critérios da fase anterior forem atendidos.

---

## Não adicionar funcionalidades fora do escopo

Novas ideias devem ser documentadas e avaliadas antes da implementação.

---

## Manter o produto funcionando

Após cada fase:

- Código deve executar.
- Testes devem passar.
- Banco deve estar consistente.

---

# 12. Ordem Resumida

```
Fase 0
Preparação

↓

Fase 1
Fundação e autenticação

↓

Fase 2
Pranchetas

↓

Fase 3
Editor tático

↓

Fase 4
Compartilhamento

↓

Fase 5
Melhorias UX

↓

Fase 6
Qualidade

↓

Fase 7
Expansão futura
```

---

# 13. Objetivo Final do Roadmap

Ao concluir as fases iniciais, o TactiBoard deve possuir um MVP funcional capaz de:

- Permitir cadastro de usuários.
- Criar análises táticas.
- Editar visualmente jogadas.
- Salvar estratégias.
- Compartilhar análises.

Esse será o ponto inicial para transformar o projeto em uma plataforma completa de análise futebolística.
