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

A autenticação é implementada utilizando:

- Laravel Breeze 2.4, stack Blade + Alpine.js + Tailwind CSS

Funcionalidades:

- Cadastro.
- Login.
- Logout.
- Recuperação de senha.

Instalado na Fase 1. As rotas ficam em `routes/auth.php`; os controllers em
`app/Http/Controllers/Auth/`; as telas em `resources/views/auth/`.

---

## 6.1 Adaptações feitas no scaffolding do Breeze

Decisão registrada na Fase 1 (2026-08-05).

O Breeze gera código funcional, porém com padrões que divergem de `docs/06`.
As adaptações abaixo são deliberadas — reinstalar o Breeze as desfaz.

### Validação em Form Requests

`docs/06` §6 determina que toda validação de entrada use Form Requests. Os
controllers do Breeze validam em linha, com `$request->validate()` e
`$request->validateWithBag()`. As regras foram movidas para:

| Form Request | Substitui a validação em |
|---|---|
| `Auth\RegisterUserRequest` | `RegisteredUserController@store` |
| `Auth\SendPasswordResetLinkRequest` | `PasswordResetLinkController@store` |
| `Auth\ResetPasswordRequest` | `NewPasswordController@store` |
| `Auth\UpdatePasswordRequest` | `PasswordController@update` |
| `Auth\ConfirmPasswordRequest` | `ConfirmablePasswordController@store` |
| `DeleteUserRequest` | `ProfileController@destroy` |

A tela de perfil tem três formulários na mesma página. Os dois que pedem senha
usam *error bag* próprio (`updatePassword` e `userDeletion`), declarado na
propriedade `$errorBag` do Form Request, para que o erro de um formulário não
apareça no outro.

`Auth\LoginRequest` e `ProfileUpdateRequest` já vinham como Form Request e foram
mantidos como estão.

### Action para o cadastro

`RegisterUserAction` concentra a criação do usuário e o disparo do evento
`Registered`. O login da sessão fica no controller: a Action não conhece sessão,
o que a mantém testável e reaproveitável.

As demais operações de autenticação continuam sem Action. Elas são chamadas
diretas a facades do framework (`Auth`, `Password`) e criar uma camada em volta
só para uniformizar contrariaria `docs/04` §20 — não criar complexidade antes da
necessidade.

### Senha sempre com hash explícito

O model `User` tem o cast `password => 'hashed'`, que já protegeria a escrita.
Ainda assim, os pontos que gravam senha usam `Hash::make()` explicitamente:
é um caminho de credencial, e uma falha silenciosa ali gravaria senha em texto
puro. Há um teste dedicado a isso em `RegistrationTest`.

### Tailwind 4 preservado

O `breeze:install` rebaixa o projeto para o Tailwind 3: cria `tailwind.config.js`
e `postcss.config.js`, adiciona `autoprefixer`/`postcss` e substitui o
`resources/css/app.css` pelas diretivas `@tailwind`.

A Fase 0 fixou o Tailwind 4. A configuração foi restaurada para o modelo
CSS-first do Tailwind 4 e os dois arquivos de config foram removidos. O plugin de
formulários do Breeze continua ativo, agora via `@plugin '@tailwindcss/forms'`
dentro do `app.css`.

### Verificação de e-mail instalada, porém inativa

O Breeze instala rotas, telas e testes de verificação de e-mail. O recurso está
**inativo**: o model `User` não implementa `MustVerifyEmail`, então o middleware
`verified` aplicado em `/dashboard` deixa passar. Não faz parte do MVP
(`docs/02` RF-001). As rotas foram mantidas para que adotar o recurso no futuro
seja apenas implementar a interface no model.

---

## 6.2 Idioma da interface

Decisão registrada na Fase 1 (2026-08-05).

O produto é destinado ao público brasileiro (`docs/01` §4), então a aplicação
roda em português:

| Variável | Valor |
|---|---|
| `APP_LOCALE` | `pt_BR` |
| `APP_FALLBACK_LOCALE` | `en` |
| `APP_FAKER_LOCALE` | `pt_BR` |

As telas do Breeze já envolvem todo texto visível em `__()`, então a tradução
vive em arquivos de idioma e **nenhuma view precisou ser editada**:

- `lang/pt_BR.json` — textos de interface.
- `lang/pt_BR/validation.php`, `auth.php`, `passwords.php` — mensagens do framework.

As traduções são **parciais por opção**: cobrem as regras alcançáveis pelas telas
existentes. Chaves ausentes caem no `APP_FALLBACK_LOCALE`, então nada quebra — os
arquivos crescem conforme novas regras entram no produto.

Isso é diferente da regra de `docs/03` §6.3, que trata de **dados**: `category` é
persistida em inglês e só o rótulo é exibido em português. O idioma da interface
não altera o que vai para o banco.

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

## 8.1 Quando o Livewire entra

Decisão registrada na Fase 2 (2026-08-05).

O Livewire **ainda não está instalado**. O CRUD de pranchetas da Fase 2 é um
fluxo de formulários sem reatividade: controller fino, Form Request, Action e
Blade resolvem inteiramente. Instalar o Livewire para isso adicionaria uma
dependência sem necessidade real, contra `docs/04` §20 e `CLAUDE.md` §3, regra 5.

Ele entra na **Fase 3**, com o editor tático — ali existe estado a sincronizar
entre navegador e servidor, que é exatamente o problema que o Livewire resolve.
Os componentes previstos (`BoardEditor`, `FieldCanvas`, `Toolbar`) pertencem a
essa fase.

O Alpine.js já está em uso desde a Fase 1, para interação local: o menu da
navbar e os modais de confirmação.

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

Por padrão, o Sail executa o PHP como o usuário interno `sail`, alinhando o uid
dele ao do usuário do host. Isso funciona bem quando a sessão do host é um
usuário comum.

Quando a sessão do host é **root**, esse padrão quebra: o uid 0 já pertence ao
root dentro do container, o `usermod -u 0 sail` falha, e qualquer alternativa
(como fixar o container em outro uid) cria um descompasso de dono entre host e
container. Esse descompasso tem duas consequências ruins:

- O container não consegue escrever nos arquivos do host, e a aplicação passa a
  responder **500** por não escrever em `storage/`.
- O Git recusa operar no repositório com *dubious ownership*, porque o dono dos
  arquivos difere do usuário que executa o comando. Essa proteção existe para
  impedir que um repositório de terceiros execute *hooks* como root.

A solução adotada é executar o PHP como root dentro do container, alinhando os
dois lados. Duas variáveis são necessárias, ambas definidas apenas no `.env`
local (que não é versionado):

| Variável | Cobre |
|---|---|
| `SUPERVISOR_PHP_USER=root` | O servidor web gerenciado pelo supervisord |
| `APP_USER=root` | Os comandos avulsos: `sail artisan`, `sail composer`, `sail pest`, `sail npm` |

Definir apenas uma delas resolve pela metade e o descompasso reaparece.

O `compose.yaml` versionado mantém o padrão do Sail
(`SUPERVISOR_PHP_USER: '${SUPERVISOR_PHP_USER:-sail}'`), e o `.env.example`
traz as duas linhas comentadas. Assim, em máquinas com usuário comum o
comportamento padrão do Sail continua valendo, sem efeito colateral — a
configuração de root é uma escolha por máquina, não do projeto.

Com isso, o Git funciona normalmente, **sem** necessidade de
`git config --global --add safe.directory`.

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
