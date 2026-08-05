# TactiBoard — Technical Architecture

## 1. Objetivo do Documento

Este documento define as decisões técnicas e arquiteturais do TactiBoard.

O objetivo é orientar o desenvolvimento do sistema garantindo:

- Organização do código.
- Facilidade de manutenção.
- Escalabilidade futura.
- Aplicação de boas práticas.
- Aproveitamento adequado do ecossistema Laravel.

Todas as decisões técnicas devem considerar o MVP atual, mas sem impedir a evolução futura do produto.

---

# 2. Stack Tecnológica

## Backend

Framework:

- Laravel 12

Linguagem:

- PHP 8.4+

Banco de dados:

- MySQL

Gerenciamento de dependências:

- Composer

---

## Frontend

Tecnologias:

- Blade Templates
- Livewire
- Alpine.js
- Tailwind CSS

---

## Motivo da escolha

O objetivo principal do projeto é aprofundar conhecimento no ecossistema Laravel.

A primeira versão deve priorizar:

- Simplicidade.
- Produtividade.
- Integração nativa com Laravel.

Uma SPA completa utilizando React ou Vue não será utilizada inicialmente.

---

# 3. Arquitetura Geral

O projeto seguirá o padrão MVC do Laravel.

Fluxo principal:

```
Request

↓

Route

↓

Controller

↓

Application Layer

↓

Model

↓

Database

↓

Response
```

---

# 4. Organização de Código

A estrutura seguirá a organização padrão do Laravel com algumas extensões.

Estrutura esperada:

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

# 5. Responsabilidade das Camadas

## Controllers

Responsabilidade:

- Receber requisições.
- Validar fluxo.
- Chamar ações necessárias.
- Retornar respostas.

Controllers não devem conter:

- Regras complexas.
- Cálculos.
- Manipulação extensa de dados.

---

## Models

Responsabilidade:

- Representar entidades do banco.
- Definir relacionamentos.
- Definir casts.
- Possuir pequenas regras relacionadas ao próprio modelo.

---

## Form Requests

Toda validação de entrada deve utilizar Form Requests.

Exemplo:


CreateBoardRequest
UpdateBoardRequest


Evitar validações diretamente dentro dos Controllers.

---

## Actions

Actions serão utilizadas para operações importantes do sistema.

Exemplos:


CreateBoardAction

UpdateBoardCanvasAction

GenerateSharedLinkAction


Uma Action deve representar uma operação específica.

---

## Services

Services serão utilizados quando existir lógica complexa ou integração externa.

Exemplos futuros:


CanvasExportService

NotificationService

AIAnalysisService


Não criar Services sem necessidade.

---

# 6. Autenticação

A autenticação será implementada utilizando:

- Laravel Breeze

Funcionalidades:

- Cadastro.
- Login.
- Logout.
- Recuperação de senha.

---

# 7. Autorização

O sistema utilizará:

- Policies.
- Gates quando necessário.

Exemplo:


BoardPolicy

view()

update()

delete()


Regra principal:

Um usuário somente pode modificar suas próprias pranchetas.

---

# 8. Estratégia do Editor Tático

O editor é o componente central do produto.

A primeira versão será construída utilizando:

- Livewire para comunicação com backend.
- Alpine.js para interações rápidas no frontend.

---

# 9. Persistência do Canvas

O conteúdo visual da prancheta será armazenado em JSON.

Exemplo:

```json
{
    "elements": [
        {
            "type": "player",
            "x": 100,
            "y": 200,
            "team": "home"
        }
    ]
}
```

---

## Motivo

O editor possui elementos diferentes:

- Jogadores.
- Bolas.
- Setas.
- Textos.
- Cones.

Uma modelagem relacional separada para cada elemento adicionaria complexidade desnecessária no MVP.

---

# 10. Componentização Frontend

Componentes devem ser pequenos e possuir responsabilidades claras.

Exemplos:

- FieldCanvas
- PlayerElement
- ArrowElement
- Toolbar
- BoardEditor

---

# 11. Banco de Dados

As migrations devem ser criadas utilizando o padrão Laravel.

Regras:

- Sempre utilizar migrations.
- Nunca alterar banco manualmente em produção.
- Relacionamentos devem possuir foreign keys quando aplicável.

---

# 12. Testes

O projeto deve utilizar testes automatizados.

Framework:

Pest.

Decisão tomada por ser o framework padrão do Laravel 12, com sintaxe mais concisa e integração direta com o ecossistema.

---

## Tipos de testes

### Feature Tests

Para fluxos completos.

Exemplos:

- Criar usuário.
- Criar prancheta.
- Compartilhar análise.

---

### Unit Tests

Para regras isoladas.

Exemplos:

- Geradores.
- Serviços.
- Classes auxiliares.

---

# 13. Filas e Jobs

Não serão obrigatórios no MVP inicial.

Porém a arquitetura deve permitir utilização futura.

Possíveis usos:

- Exportação de PDF.
- Geração de imagens.
- Processamento de vídeos.
- Integrações externas.

---

# 14. Eventos

Eventos serão utilizados quando uma ação gerar efeitos secundários.

Exemplo futuro:

Evento:

BoardShared

Listeners:

- SendNotification
- RegisterActivity

---

# 15. Cache

Cache não será utilizado inicialmente.

Possíveis aplicações futuras:

- Biblioteca pública.
- Rankings.
- Estatísticas.

---

# 16. APIs

O MVP não dependerá de API externa.

Porém a arquitetura deve permitir criação futura de API REST.

Possíveis endpoints:

- GET /api/boards
- POST /api/boards
- GET /api/boards/{id}

Autenticação futura:

Laravel Sanctum.

---

# 17. Upload de Arquivos

Uploads futuros devem utilizar o sistema Storage do Laravel.

Exemplos:

- Fotos.
- Logos.
- Vídeos.

Nunca armazenar arquivos diretamente no banco.

---

# 18. Ambiente de Desenvolvimento

O projeto deve utilizar:

- Laravel Sail sobre Docker.
- .env para configurações.
- Git para versionamento.

O ambiente local roda em containers Docker gerenciados pelo Laravel Sail, garantindo que PHP, MySQL e demais serviços sejam idênticos para qualquer pessoa que trabalhe no projeto.

Comandos de desenvolvimento devem ser executados através do Sail.

Exemplo:

```
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
./vendor/bin/sail pest
```

---

## 18.1 Portas e exposição de rede

Decisão registrada na Fase 0 (2026-08-05).

As portas padrão do Sail não puderam ser usadas na máquina de desenvolvimento:
a porta 80 já era servida por outro container e a 3306 por um MySQL local.

Configuração adotada, definida no `.env`:

| Variável | Valor | Motivo |
|---|---|---|
| `APP_PORT` | `8080` | A porta 80 está ocupada |
| `FORWARD_DB_PORT` | `33061` | A porta 3306 está ocupada |
| `APP_BIND` | `127.0.0.1` | Interface em que a aplicação e o Vite são publicados |

O `APP_BIND` controla apenas a aplicação e o Vite. A porta do MySQL está fixada
em `127.0.0.1` diretamente no `compose.yaml`, de propósito: nada fora do host
precisa alcançar o banco, e a aplicação o acessa pela rede interna `sail`.
Mudar `APP_BIND` para `0.0.0.0` **não** expõe o banco.

`DB_HOST=mysql` e `DB_PORT=3306` permanecem inalterados: são endereços internos
da rede Docker `sail`. Só muda a porta publicada no host.

### Por que publicar apenas em loopback

O Docker insere suas regras de encaminhamento **antes** da cadeia `INPUT` do
UFW. Uma porta publicada em `0.0.0.0` fica acessível pela internet mesmo com o
firewall configurado para bloquear — o que foi confirmado na prática: o MySQL
respondia a conexões vindas do IP público com `root` e a senha padrão do Sail,
e a aplicação respondia com `APP_DEBUG=true`, o que expõe o conteúdo do `.env`
nas páginas de erro.

Por isso todas as portas são publicadas em `127.0.0.1`. Para acessar a
aplicação de outra máquina, usar túnel SSH:

```
ssh -L 8080:127.0.0.1:8080 <host>
```

Expor de verdade exige mudar `APP_BIND` para `0.0.0.0` deliberadamente — e,
nesse caso, revisar antes `APP_DEBUG` e a senha do banco.

---

## 18.2 Usuário do container

O container da aplicação roda como uid **1337** (`WWWUSER`/`WWWGROUP` no
`.env`), e os arquivos do projeto pertencem a esse uid.

O padrão do Sail é usar o uid de quem executa o comando. Como o ambiente desta
máquina opera como root, o valor seria `0` e o `usermod -u 0 sail` executado na
inicialização do container falharia, pois o uid 0 já pertence ao root.

O script do Sail carrega o `.env` antes de aplicar seus próprios defaults, então
basta manter `WWWUSER` e `WWWGROUP` definidos ali — nenhuma exportação manual é
necessária. Se o `.env` for recriado a partir de um `.env.example` sem essas
chaves, o container volta a falhar ao subir.

Consequência prática adicional: o Git pode recusar operações com *dubious
ownership*, porque o dono dos arquivos difere do usuário da sessão. Resolver uma
vez com:

```
git config --global --add safe.directory /root/tactiboard
```

---

# 19. Qualidade de Código

Regras:

- Código deve seguir padrões PSR.
- Evitar duplicação.
- Criar testes para novas funcionalidades.
- Utilizar nomes claros.
- Preferir código simples e legível.

---

# 20. Princípios Arquiteturais

## Não criar complexidade antes da necessidade

Evitar:

- Abstrações prematuras.
- Design Patterns sem necessidade.
- Camadas que não possuem responsabilidade real.

---

## Código preparado para evolução

O MVP deve ser simples, mas permitir crescimento futuro.

---

## Produto antes de tecnologia

Decisões técnicas devem sempre apoiar a experiência do usuário e os objetivos do produto.

---

# 21. Resumo Técnico

Stack:

- Laravel 12
- PHP 8.4
- MySQL
- Blade
- Livewire
- Alpine.js
- Tailwind CSS

Arquitetura:

```
MVC Laravel

+
Actions

+
Policies

+
Livewire Components
```

Persistência do editor:

JSON Canvas

Objetivo:

Construir uma base sólida para evoluir o TactiBoard de um MVP para uma plataforma completa de análise tática.
