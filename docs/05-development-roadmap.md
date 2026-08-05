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

## Objetivo

Criar a base inicial do ambiente de desenvolvimento.

---

## Tarefas

- Criar projeto Laravel.
- Configurar ambiente local com Laravel Sail e Docker.
- Configurar banco de dados.
- Configurar controle de versão.
- Configurar variáveis de ambiente.
- Instalar dependências iniciais.
- Configurar Pest.
- Configurar Tailwind CSS.

---

## Critérios de conclusão

- Aplicação Laravel executando localmente através do Sail.
- Banco conectado.
- Estrutura inicial funcionando.
- Suíte de testes executando com Pest.
- Projeto versionado no Git.

---

# 4. Fase 1 — Fundação da Aplicação

## Objetivo

Criar a estrutura base utilizada por todo o sistema.

---

## Funcionalidades

### Autenticação

Implementar:

- Cadastro.
- Login.
- Logout.
- Recuperação de senha.

---

### Layout Base

Criar:

- Navbar.
- Área autenticada.
- Dashboard inicial.
- Componentes visuais básicos.

---

### Usuário

Implementar:

- Perfil básico.
- Dados do usuário.

---

## Critérios de conclusão

Usuário consegue:

- Criar uma conta.
- Entrar no sistema.
- Acessar dashboard.
- Encerrar sessão.

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
