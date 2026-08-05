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

## Exemplo de estrutura

```json
{
    "elements": [
        {
            "type": "player",
            "team": "home",
            "number": 9,
            "x": 200,
            "y": 350
        },
        {
            "type": "arrow",
            "start": {
                "x": 200,
                "y": 350
            },
            "end": {
                "x": 300,
                "y": 250
            }
        },
        {
            "type": "text",
            "content": "Atacar profundidade",
            "x": 400,
            "y": 200
        }
    ]
}
```

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
