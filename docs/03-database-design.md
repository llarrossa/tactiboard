# TactiBoard — Database Design

## 1. Objetivo do Documento

Este documento define a modelagem inicial do banco de dados do TactiBoard.

O objetivo é documentar:

- Entidades principais.
- Campos.
- Relacionamentos.
- Regras de persistência.
- Decisões arquiteturais.

A modelagem deve atender ao MVP e permitir evolução futura sem grandes alterações estruturais.

---

# 2. Banco de Dados

## Tecnologia

Banco de dados relacional:

- MySQL

O Laravel será responsável pelo gerenciamento das migrations, models e relacionamentos.

---

# 3. Princípios de Modelagem

## Simplicidade inicial

O MVP deve utilizar poucas entidades e evitar complexidade desnecessária.

Não devem ser criadas tabelas ou abstrações para funcionalidades que ainda não existem no produto.

---

## Evolução futura

A estrutura deve permitir expansão para:

- Times.
- Organizações.
- Colaboração entre usuários.
- Permissões avançadas.
- Biblioteca de jogadas.
- Histórico de versões.
- Gestão de treinamentos.

---

# 4. Entidades Principais

O MVP possui três entidades principais:

- User
- Board
- SharedLink

Relacionamento:

```
User
|
| 1:N
|
Board
|
| 1:N
|
SharedLink
```

---

# 5. Entidade User

## Descrição

Representa uma pessoa cadastrada na plataforma.

A tabela `users` utilizará inicialmente a estrutura padrão fornecida pelo Laravel.

---

## Campos

Tabela:


users


| Campo | Tipo | Descrição |
|---|---|---|
| id | bigint | Identificador único |
| name | varchar | Nome do usuário |
| email | varchar | Email utilizado para login |
| password | varchar | Senha criptografada |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

---

## Regras

- O email deve ser único.
- A senha nunca deve ser armazenada em texto puro.
- Um usuário pode possuir várias pranchetas.
- Um usuário somente pode acessar recursos que possui permissão.

---

## Relacionamentos


User hasMany Boards


---

# 6. Entidade Board

## Descrição

Representa uma prancheta tática criada pelo usuário.

A prancheta é o principal recurso do sistema.

Exemplos:

- Saída de bola 4-3-3.
- Pressão alta.
- Organização defensiva.
- Escanteio ofensivo.
- Exercício de treinamento.

---

## Campos

Tabela:


boards


| Campo | Tipo | Descrição |
|---|---|---|
| id | bigint | Identificador único |
| user_id | bigint | Usuário proprietário |
| title | varchar | Nome da análise |
| description | text nullable | Descrição da análise |
| category | varchar | Categoria da análise |
| canvas_data | json | Dados do editor visual |
| visibility | varchar | Controle de visibilidade |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

---

# 6.1 Campo canvas_data

## Decisão arquitetural

O conteúdo visual da prancheta será armazenado como JSON.

A escolha foi feita porque o editor possui diferentes tipos de elementos:

- Jogadores.
- Bola.
- Setas.
- Textos.
- Cones.

Criar uma tabela individual para cada elemento aumentaria a complexidade do MVP sem trazer benefícios neste momento.

---

## Sistema de coordenadas

Decisão registrada na Fase 3 (2026-08-05).

As coordenadas são expressas no **próprio campo**, não em pixels de tela:

| | |
|---|---|
| Origem | canto superior esquerdo do gramado |
| Eixo x | `0` a `1050` |
| Eixo y | `0` a `680` |
| Unidade | decímetro — o campo tem as medidas oficiais da IFAB, 105 m × 68 m |

O editor desenha o campo em SVG com esse sistema de coordenadas e deixa o CSS
escalar o desenho. Assim o JSON **não depende da resolução da tela**: a mesma
prancheta abre igual no notebook e no celular, e a responsividade da Fase 5 não
exige reprocessar nenhum canvas já gravado.

O `viewBox` do SVG é maior que o gramado para caber as traves e uma margem de
grama. Isso não altera o sistema de coordenadas dos elementos, que permanece
`0..1050` por `0..680`. Um elemento arrastado para fora não vira erro: ele para
na borda.

Os limites são definidos em `App\Rules\CanvasRules` (`FIELD_WIDTH` e
`FIELD_HEIGHT`), consumidos tanto pelo desenho quanto pela validação.

---

## Schema dos elementos

Esta seção é a **referência oficial do formato persistido**.

`canvas_data` é sempre um objeto com uma única chave `elements`, cuja lista
contém no máximo **100** elementos.

Todo elemento tem:

| Campo | Tipo | Descrição |
|---|---|---|
| `id` | string (até 36) | Identificador do elemento dentro da prancheta |
| `type` | string | `player`, `ball`, `cone`, `text` ou `arrow` |

O `id` é gerado ao criar o elemento e o acompanha por toda a vida dele. Ele
existe porque **nenhuma operação pode depender da posição na lista**: remover um
elemento do meio reindexa o array, e o editor trocaria um elemento por outro na
tela. Os ids são únicos dentro da prancheta.

Os demais campos dependem do tipo:

| Tipo | Campos próprios |
|---|---|
| `player` | `x`, `y`, `team` (`home` \| `away`), `number` (1 a 99) |
| `ball` | `x`, `y` |
| `cone` | `x`, `y` |
| `text` | `x`, `y`, `content` (até 120 caracteres) |
| `arrow` | `start` e `end`, cada um `{ "x": …, "y": … }` |

A seta é o único tipo **não posicional**: ela é definida por dois pontos e não
tem `x`/`y` soltos. Jogador do time e adversário são o mesmo tipo `player`,
distinguidos por `team` — são a mesma peça em cores diferentes, não dois
conceitos.

Um elemento guarda **apenas** as chaves do seu tipo. Chave que sobrou de uma
edição anterior é descartada na gravação, não persistida.

```json
{
    "elements": [
        { "id": "k3Ba9xQ2mZ", "type": "player", "team": "home", "number": 9, "x": 200, "y": 350 },
        { "id": "p7Tc1vLd4R", "type": "player", "team": "away", "number": 4, "x": 800, "y": 350 },
        { "id": "b2Nf8sWq0E", "type": "ball", "x": 525, "y": 340 },
        { "id": "c5Hj3yUr6T", "type": "cone", "x": 300, "y": 200 },
        { "id": "t9Zx4mKp1A", "type": "text", "content": "Atacar profundidade", "x": 400, "y": 200 },
        {
            "id": "a1Qw6nJb8S",
            "type": "arrow",
            "start": { "x": 200, "y": 350 },
            "end": { "x": 300, "y": 250 }
        }
    ]
}
```

As coordenadas são gravadas com **uma casa decimal**: o arrasto produz frações
longas de pixel convertido, e um décimo de metro já é mais fino que o olho no
campo.

---

## Estado inicial e restrições

Decisão registrada na Fase 2 (2026-08-05).

A coluna é `NOT NULL`. Uma prancheta recém-criada nasce com o canvas vazio:

```json
{ "elements": [] }
```

O padrão é definido no model (`$attributes`), não no banco — colunas `json` do
MySQL não aceitam DEFAULT literal, e manter a regra no model funciona igual em
qualquer driver.

O motivo de não permitir `null`: o editor da Fase 3 passa a poder assumir que a
estrutura sempre existe, sem espalhar verificação de ausência pelo código.

## Ordem das chaves não é preservada

O MySQL normaliza objetos JSON ao gravar: o que volta tem o mesmo conteúdo, mas
não necessariamente na mesma ordem de chaves.

Nenhuma lógica pode depender dessa ordem. Em teste, comparar canvas por
igualdade de conteúdo, nunca por identidade estrita.

---

# 6.2 Campo visibility

O campo visibility controla a disponibilidade da prancheta.

Valores possíveis:

- private
- public

---

## Private

Apenas o proprietário da prancheta pode acessar.

---

## Public

A prancheta pode ser visualizada através de um link compartilhado.

---

## Relação com shared_links

O compartilhamento utiliza dois mecanismos com responsabilidades separadas:

- `boards.visibility` controla o estado da prancheta: pública ou privada.
- `shared_links.token` controla o acesso: é o token que permite abrir a prancheta pela URL.

O campo visibility define **se** a prancheta pode ser acessada publicamente.

O token define **por onde** esse acesso acontece.

Consequências:

- Uma prancheta `private` não é acessível, mesmo que possua um token válido.
- Um token inexistente ou inválido não dá acesso, mesmo que a prancheta seja `public`.
- Tornar uma prancheta `private` novamente revoga o acesso de todos os links existentes sem removê-los.

---

# 6.3 Campo category

No MVP, a categoria será armazenada diretamente como texto.

Valores iniciais:

- attack
- defense
- set_piece
- training
- other

---

## Decisão

Não será criada uma tabela específica para categorias inicialmente.

Motivos:

- Quantidade pequena de categorias.
- Baixa complexidade.
- Maior flexibilidade para mudanças futuras.

Caso o produto evolua para uma biblioteca avançada de conteúdos, essa estrutura poderá ser alterada.

---

# 7. Entidade SharedLink

## Descrição

Representa um link público criado para compartilhar uma prancheta.

---

## Campos

Tabela:

shared_links

| Campo | Tipo | Descrição |
|---|---|---|
| id | bigint | Identificador único |
| board_id | bigint | Prancheta relacionada |
| token | varchar | Código público do link |
| expires_at | timestamp nullable | Data de expiração opcional |
| created_at | timestamp | Data de criação |
| updated_at | timestamp | Data de atualização |

---

# 7.1 Campo token

Exemplo:

https://tactiboard.com/share/a8f92kd3

O token deve:

- Ser único.
- Não expor IDs internos.
- Ser difícil de prever.
- Permitir acesso público controlado.

---

## 7.2 Regra de acesso público

Um visitante sem conta só visualiza a prancheta quando todas as condições forem atendidas:

- O token existe.
- A prancheta relacionada possui visibility `public`.
- O campo expires_at está vazio ou ainda não foi atingido.

Qualquer condição não atendida deve negar o acesso.

O acesso público é sempre somente leitura.

---

# 8. Relacionamentos

## User → Board

Tipo:

1:N

Um usuário possui várias pranchetas.

Exemplo:

```
User
 ├── Board 1
 ├── Board 2
 └── Board 3
```

---

## Board → SharedLink

Tipo:

1:N

Uma prancheta pode possuir vários links compartilhados.

Exemplo:

```
Board
 ├── Link público 1
 └── Link público 2
```

---

# 9. Índices

## users

Índice:

email UNIQUE

Motivo:

Garantir que não existam usuários duplicados.

---

## boards

Índices:

- user_id INDEX
- visibility INDEX
- created_at INDEX

Motivos:

- Buscar pranchetas de um usuário.
- Filtrar por visibilidade.
- Ordenar por data.

---

## shared_links

Índices:

- board_id INDEX
- token UNIQUE

Motivos:

- Encontrar links de uma prancheta.
- Garantir unicidade do token.

---

# 10. Soft Delete

## Decisão inicial

O MVP não utilizará Soft Delete.

Motivos:

- Menor complexidade.
- Não existe necessidade inicial de recuperação de dados.
- O foco é validar o produto.

---

## Possível evolução

Soft Delete poderá ser adicionado futuramente para:

- Planos pagos.
- Auditoria.
- Recuperação de dados.
- Histórico.

---

# 11. Histórico de Alterações

Não será implementado no MVP.

Porém, a arquitetura deve permitir uma futura implementação.

Possível entidade:

board_versions

Exemplo:

Board

- Versão 1
- Versão 2
- Versão 3

Possíveis funcionalidades futuras:

- Restaurar versões antigas.
- Comparar alterações.
- Histórico de edição.

---

# 12. Futuras Entidades

As entidades abaixo não fazem parte do MVP, mas devem ser consideradas na evolução.

---

## Team

Representa uma equipe de futebol.

Possíveis campos:

- id
- name
- logo
- owner_id

---

## Organization

Representa clubes, escolinhas ou grupos.

Exemplo:

- Escolinha Sub-17
- Comissão Técnica
- Clube amador

---

## Board Collaborators

Permite múltiplos usuários trabalhando na mesma prancheta.

Possível estrutura:

board_user

- board_id
- user_id
- permission

---

## Training Session

Representa uma sessão de treinamento.

Possíveis campos:

- id
- team_id
- date
- description

---

# 13. Regras de Integridade

## Board

Obrigatório:

- Possuir um usuário proprietário.
- Possuir título.
- Possuir dados válidos do canvas.

### Exclusão em cascata

`boards.user_id` tem `ON DELETE CASCADE`: excluir a conta remove as pranchetas
do usuário. Sem isso, a exclusão de conta implementada na Fase 1 esbarraria na
foreign key ou deixaria pranchetas órfãs.

`shared_links.board_id` deverá seguir a mesma regra quando a tabela for criada
na Fase 4 — como o MVP não usa Soft Delete, um link que sobrevivesse à prancheta
continuaria acessível apontando para o nada.

---

## SharedLink

Obrigatório:

- Possuir uma prancheta relacionada.
- Possuir token único.

---

# 14. Resumo da Modelagem MVP

Estrutura inicial:

```
users

    |
    | 1:N

boards

    |
    | 1:N

shared_links
```

A modelagem inicial deve permanecer simples, utilizando JSON para representar o editor visual.

Essa abordagem permite validar rapidamente o produto e evoluir a estrutura conforme novas necessidades surgirem.
