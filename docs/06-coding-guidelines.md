# TactiBoard — Coding Guidelines

## 1. Objetivo do Documento

Este documento define os padrões de desenvolvimento utilizados no TactiBoard.

O objetivo é garantir:

- Código consistente.
- Fácil manutenção.
- Boa legibilidade.
- Facilidade de evolução.
- Padronização durante o desenvolvimento.

Todas as implementações devem seguir estas diretrizes, incluindo código criado manualmente ou por ferramentas de inteligência artificial.

---

# 2. Princípios Gerais

## Código simples antes de código complexo

A solução mais simples que resolve corretamente o problema deve ser priorizada.

Evitar:

- Abstrações desnecessárias.
- Design Patterns sem necessidade.
- Arquiteturas complexas para problemas simples.

---

## Código deve refletir o domínio do produto

Nomes e estruturas devem representar conceitos reais do sistema.

Exemplo:

Preferir:

- CreateBoardAction
- GenerateSharedLinkAction

Evitar:

- ProcessDataAction
- HandleInformationService
- ManagerClass

---

## Não duplicar lógica

Quando uma regra de negócio for utilizada em vários locais, ela deve ser extraída para uma classe reutilizável.

---

# 3. Organização de Código

A estrutura principal seguirá:

```
app/

├── Actions/
├── Enums/
├── Http/
│ ├── Controllers/
│ ├── Requests/
│ └── Resources/
├── Livewire/
├── Models/
├── Policies/
├── Services/
├── Jobs/
├── Events/
├── Listeners/
└── Notifications/
```

---

# 4. Controllers

## Responsabilidade

Controllers devem ser responsáveis por:

- Receber requisições.
- Chamar classes responsáveis pela operação.
- Retornar respostas.

---

## Não permitido

Controllers não devem conter:

- Regras complexas.
- Cálculos extensos.
- Consultas complexas.
- Processamentos grandes.

Exemplo a evitar:

```php
public function store(Request $request)
{
    // validação
    // criação de usuário
    // regras de negócio
    // envio de email
    // cálculos
}
```

---

## Preferir

```php
public function store(CreateBoardRequest $request)
{
    $board = $this->createBoardAction->execute(
        $request->validated()
    );

    return redirect()->route('boards.show', $board);
}
```

---

# 5. Models

## Responsabilidade

Models devem representar entidades do banco.

Devem conter:

- Relacionamentos.
- Casts.
- Scopes simples.
- Regras diretamente relacionadas ao próprio modelo.

---

## Evitar

Não transformar Models em classes gigantes.

Evitar colocar:

- Fluxos completos.
- Integrações externas.
- Processamentos complexos.

---

# 6. Form Requests

Toda validação de entrada deve utilizar Form Requests.

Exemplos:

- CreateBoardRequest
- UpdateBoardRequest

Benefícios:

- Controllers menores.
- Validações reutilizáveis.
- Código mais organizado.

## Exceção: o canvas

Decisão registrada na Fase 3 (2026-08-05).

O salvamento do canvas acontece pelo Livewire, que valida dentro do componente e
não passa por Form Request. As regras vivem em `App\Rules\CanvasRules`, classe
única reutilizada por todo ponto que precise validar um canvas.

O motivo e as consequências estão em `04-technical-architecture.md` §8.3. Essa é
a única exceção; toda validação que chega por HTTP continua em Form Request.

---

# 7. Actions

## Objetivo

Actions representam operações específicas do sistema.

Uma Action deve responder:

"O que o usuário está tentando fazer?"

---

## Exemplos

- CreateBoardAction
- UpdateBoardCanvasAction
- DeleteBoardAction
- GenerateSharedLinkAction

---

## Regras

Uma Action deve:

- Ter uma responsabilidade clara.
- Executar uma operação completa.
- Ser facilmente testável.

---

# 8. Services

Services devem ser utilizados quando existir lógica complexa ou integrações.

Exemplos:

- CanvasExportService
- ImageGenerationService
- NotificationService

Não criar Services apenas para substituir Controllers.

---

# 9. Livewire Components

O frontend interativo será desenvolvido utilizando Livewire.

---

## Regras

Componentes devem possuir responsabilidades pequenas.

Exemplos:

- BoardEditor
- BoardList
- BoardForm
- Toolbar

Evitar:

Um componente contendo:

- Toda página.
- Toda regra.
- Todo comportamento.

---

# 10. JavaScript e Alpine.js

Alpine.js deve ser utilizado para comportamentos pequenos de interface.

Exemplos:

- Abrir menus.
- Controlar estados simples.
- Interações locais.

Não utilizar JavaScript puro para substituir funcionalidades que pertencem ao Livewire.

---

# 11. Banco de Dados

## Migrations

Todas alterações no banco devem utilizar migrations.

Nunca alterar banco manualmente.

---

## Nomes

Utilizar padrão Laravel:

Tabelas:

- boards
- shared_links

Models:

- Board
- SharedLink

---

## Relacionamentos

Sempre definir relacionamentos nos Models.

Exemplo:

```php
public function boards()
{
    return $this->hasMany(Board::class);
}
```

---

# 12. Banco JSON

O campo canvas_data utiliza JSON.

Regras:

- Manter estrutura documentada.
- Evitar dados redundantes.
- Não armazenar informações que devem ser entidades próprias.

---

# 13. Testes

Toda funcionalidade relevante deve possuir testes.

O framework utilizado é o Pest.

Executar através do Sail:

```
./vendor/bin/sail pest
```

---

## Feature Tests

Utilizar para:

- Fluxos de usuário.
- Requisições HTTP.
- Permissões.

Exemplos:

- CreateBoardTest
- ShareBoardTest

---

## Unit Tests

Utilizar para:

- Classes isoladas.
- Regras específicas.

Exemplos:

- `tests/Unit/Enums/` — os quatro enums do domínio: `BoardCategory`,
  `BoardVisibility`, `CanvasElementType` e `PlayerTeam`.
- `tests/Unit/Rules/CanvasRulesTest.php` — as regras do canvas.

Os testes de enum conferem os **valores persistidos**, e não só os rótulos:
`player`, `home`, `attack` e companhia vão para dentro de `canvas_data` e da
coluna `category`, então mudá-los invalidaria o que já está gravado.

O `CanvasRulesTest` roda o validator de verdade, com `Validator::make`, em vez de
apenas conferir o conteúdo do array de regras. Inspecionar strings não pegaria
erro na expansão dos curingas (`elements.*.x`) nem na semântica de
`exclude_if`/`exclude_unless`, que é justamente onde o schema é frágil.

O diretório `tests/Unit` contém um `.gitkeep` porque a suíte **não roda** se o
diretório declarado no `phpunit.xml` não existir — a execução aborta com
`Test directory not found`. Manter o arquivo evita que isso volte caso os testes
de unidade sejam movidos algum dia.

---

## Cobertura

Decisão registrada na Fase 6 (2026-08-06).

A suíte deve manter **pelo menos 97% de cobertura de linhas**, medida por
`sail pest --coverage`. O número não é meta de vaidade: abaixo dele começam a
ficar de fora justamente os caminhos que ninguém exercita à mão — bloqueio de
tentativas, guardas de payload adulterado, ramos de erro.

O que ficar descoberto precisa ser **decisão registrada**, e não esquecimento. A
única exclusão hoje são os controllers de verificação de e-mail, recurso
instalado pelo Breeze e inativo por opção desde a Fase 1 (`docs/04` §6.1):
testá-lo cobriria código que o produto não usa.

---

## Idioma das descrições

As descrições dos testes são escritas em **português**, como o restante da
documentação do projeto:

```php
test('um usuario nao entra com a senha errada', function () { ... });
```

Os testes gerados pelo Breeze na Fase 1 foram traduzidos para manter a suíte
coerente em um só idioma.

---

# 14. Testes obrigatórios

Toda nova funcionalidade deve validar:

- Cenário de sucesso.
- Cenário de erro.
- Controle de permissões.

---

# 15. Tratamento de Erros

Erros devem ser tratados de forma clara.

Evitar:

- Silenciar exceções.
- Retornar mensagens genéricas sem contexto.

---

# 16. Nomenclatura

## Classes

PascalCase:

CreateBoardAction

---

## Métodos

camelCase:

createBoard()

---

## Variáveis

camelCase:

- $boardData
- $userId

---

# 17. Comentários

Comentários devem explicar decisões complexas.

Evitar comentários óbvios.

Ruim:

```php
// cria usuário
$user = User::create();
```

Bom:

```php
// Mantemos o canvas como JSON para permitir evolução do editor
// sem criar múltiplas tabelas para cada tipo de elemento.
```

---

# 18. Git

## Commits

Commits devem ser pequenos e objetivos.

### Idioma

As mensagens de commit são escritas em **português (pt-BR)**.

O prefixo do tipo permanece em inglês, por ser convenção consolidada:
`feat`, `fix`, `refactor`, `chore`, `docs`, `test`.

A descrição vem em português, começando em minúscula e sem ponto final.

Exemplos:

- feat: cria o model Board
- feat: adiciona a policy de autorização das pranchetas
- fix: impede acesso a prancheta de outro usuário
- refactor: extrai a geração de token para uma Action
- test: cobre as permissões de edição de prancheta

Evitar:

- feat: create board model (descrição em inglês)
- Feat: Cria o model Board. (maiúscula e ponto final)
- ajustes (sem prefixo de tipo e vago demais)

---

## Autoria dos commits

Commits não devem incluir ferramentas de IA como coautoras.

A regra completa está definida em `07-ai-development-guide.md`, seção 16 — Commits e Autoria.

---

## Branches

Utilizar:

- feature/nome-da-funcionalidade
- fix/nome-do-problema

---

# 19. Código gerado por IA

Quando utilizar ferramentas como Claude Code:

A IA deve:

- Ler todos os documentos da pasta docs.
- Seguir os padrões definidos.
- Não alterar arquitetura sem aprovação.
- Criar testes junto com funcionalidades.
- Explicar decisões relevantes.

A IA não deve:

- Criar funcionalidades fora do roadmap.
- Introduzir bibliotecas sem necessidade.
- Refatorar grandes áreas sem solicitação.
- Ignorar padrões existentes.

---

# 20. Revisão antes de finalizar tarefas

Antes de considerar uma tarefa concluída:

Verificar:

- Código segue padrões.
- Testes passam.
- Não existe duplicação.
- Não existem erros de sintaxe.
- Banco está consistente.
- Documentação está atualizada quando necessário.

---

# 21. Objetivo Final

O objetivo destas regras é manter o TactiBoard como um projeto profissional, organizado e preparado para crescer de um MVP para uma plataforma completa de análise tática de futebol.
