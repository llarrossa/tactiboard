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

## Objetivo

Criar o núcleo inicial do produto.

---

## Funcionalidades

Implementar:

- Criar prancheta.
- Listar pranchetas.
- Visualizar detalhes.
- Editar informações.
- Excluir prancheta.

---

## Banco

Criar:

- Migration boards.
- Model Board.
- Relacionamento User → Boards.

---

## Segurança

Implementar:

- BoardPolicy.
- Controle de propriedade.

---

## Testes

Criar testes para:

- Criação de prancheta.
- Listagem.
- Edição.
- Exclusão.
- Permissões.

---

## Critérios de conclusão

Usuário consegue gerenciar suas próprias pranchetas.

---

# 6. Fase 3 — Editor Tático MVP

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

- Abrir uma prancheta.
- Criar uma jogada visual.
- Salvar.
- Fechar.
- Abrir novamente mantendo a configuração.

---

# 7. Fase 4 — Compartilhamento Público

## Objetivo

Permitir que análises sejam compartilhadas externamente.

---

## Funcionalidades

Implementar:

- Criar link público.
- Visualização pública.
- Controle de acesso.

---

## Banco

Criar:

- Migration shared_links.
- Model SharedLink.

---

## Segurança

Garantir:

- Visitante não consegue editar.
- Apenas visualizar.
- Prancheta privada nega acesso mesmo com token válido.
- Token inválido nega acesso mesmo com prancheta pública.

---

## Critérios de conclusão

Uma pessoa sem conta consegue acessar uma análise através de um link.

---

# 8. Fase 5 — Melhorias de Experiência

## Objetivo

Melhorar usabilidade do produto.

---

## Funcionalidades possíveis

- Melhorar toolbar.
- Atalhos de teclado.
- Duplicar elementos.
- Limpar campo.
- Modelos pré-configurados.
- Melhorar responsividade.

---

## Critério

O editor deve ser confortável para uso real.

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
