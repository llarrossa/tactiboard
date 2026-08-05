# TactiBoard — Requisitos do MVP

## 1. Objetivo do Documento

Este documento define os requisitos funcionais e regras de negócio da primeira versão do TactiBoard.

O objetivo do MVP é validar a principal proposta de valor do produto:

> Permitir que treinadores e analistas criem, organizem e compartilhem análises táticas de futebol de forma visual e simples.

Este documento deve ser utilizado como referência durante o desenvolvimento para evitar implementação de funcionalidades fora do escopo inicial.

---

# 2. Escopo do MVP

O MVP contempla:

- Cadastro e autenticação de usuários.
- Área privada do usuário.
- Criação e gerenciamento de pranchetas táticas.
- Editor visual de análises táticas.
- Persistência das informações da prancheta.
- Compartilhamento público de análises.

---

# 3. Funcionalidades Fora do MVP

As funcionalidades abaixo não fazem parte da primeira versão:

- Animações de movimentação.
- Colaboração em tempo real.
- Upload de vídeos.
- Inteligência artificial.
- Marketplace de conteúdos.
- Aplicativo mobile.
- Pagamentos.
- Planos e assinaturas.
- Estatísticas de jogadores.
- Integração com APIs externas de futebol.

Essas funcionalidades poderão ser avaliadas em versões futuras.

---

# 4. Usuários

## 4.1 Usuário comum

Representa qualquer pessoa cadastrada na plataforma.

Pode:

- Criar uma conta.
- Fazer login.
- Gerenciar suas pranchetas.
- Editar suas próprias análises.
- Compartilhar suas análises.

---

# 5. Autenticação

## RF-001 — Cadastro de usuário

### Descrição

O sistema deve permitir que novos usuários criem uma conta.

### Dados obrigatórios:

- Nome.
- Email.
- Senha.
- Confirmação de senha.

### Regras:

- O email deve ser único.
- A senha deve seguir as regras padrão de segurança definidas pela aplicação.
- Usuários cadastrados devem conseguir acessar a área privada.

---

## RF-002 — Login

### Descrição

O usuário deve conseguir acessar sua conta utilizando suas credenciais.

### Regras:

- Usuário autenticado deve acessar o dashboard.
- Usuário não autenticado não deve acessar páginas privadas.

---

## RF-003 — Logout

O usuário deve conseguir encerrar sua sessão.

---

# 6. Dashboard

## RF-004 — Visualização do dashboard

Após autenticação, o usuário deve visualizar uma página inicial contendo:

- Lista das suas pranchetas.
- Botão para criar nova prancheta.
- Informações básicas das análises existentes.

---

# 7. Pranchetas Táticas

A prancheta é o principal recurso do sistema.

Ela representa uma análise tática de futebol.

Exemplos:

- Saída de bola 4-3-3.
- Pressão alta.
- Organização defensiva.
- Escanteio ofensivo.
- Exercício de treinamento.

---

## RF-005 — Criar prancheta

### Descrição

O usuário deve conseguir criar uma nova prancheta.

### Campos:

- Nome.
- Descrição.
- Categoria.

### Categorias iniciais:

- Ataque.
- Defesa.
- Bola parada.
- Treinamento.
- Outros.

### Regras:

- A prancheta deve pertencer ao usuário criador.
- O usuário deve informar um nome obrigatório.

---

## RF-006 — Listar pranchetas

O usuário deve visualizar suas próprias pranchetas.

Informações exibidas:

- Nome.
- Categoria.
- Data de criação.
- Última atualização.

### Regras:

- Usuário só visualiza suas próprias pranchetas na área privada.
- Pranchetas de outros usuários não devem aparecer.

---

## RF-007 — Editar prancheta

O usuário deve conseguir alterar:

- Nome.
- Descrição.
- Categoria.
- Conteúdo visual do editor.

---

## RF-008 — Excluir prancheta

O usuário deve conseguir remover uma prancheta.

### Regras:

- Apenas o proprietário pode excluir.
- A exclusão deve impedir acesso futuro à análise.

---

# 8. Editor Tático

O editor é o componente principal do produto.

Ele representa visualmente um campo de futebol onde o usuário cria sua análise.

---

## RF-009 — Exibir campo de futebol

O sistema deve disponibilizar uma área visual representando um campo.

O campo deve possuir:

- Linhas do campo.
- Áreas.
- Meio campo.
- Grandes áreas.
- Pequenas áreas.

---

## RF-010 — Adicionar jogadores

O usuário deve conseguir adicionar jogadores ao campo.

Tipos:

- Jogadores do próprio time.
- Jogadores adversários.

Cada jogador deve possuir:

- Número.
- Identificação visual por equipe.
- Posição no campo.

---

## RF-011 — Movimentar jogadores

O usuário deve conseguir:

- Arrastar jogadores.
- Alterar suas posições.
- Remover jogadores.

---

## RF-012 — Adicionar elementos auxiliares

O usuário deve conseguir adicionar:

### Bola

Representação visual da bola.

---

### Cone

Utilizado para representar exercícios.

---

### Texto

Permitir adicionar observações.

Exemplos:

- "Atacar espaço".
- "Cobertura defensiva".

---

### Seta

Permitir indicar:

- Movimentações.
- Direções.
- Passes.

---

## RF-013 — Salvar análise visual

O sistema deve salvar o estado atual do editor.

O conteúdo visual deve ser persistido para que o usuário consiga continuar editando posteriormente.

---

# 9. Compartilhamento

## RF-014 — Gerar link público

O usuário deve conseguir criar um link para compartilhar uma prancheta.

Exemplo: https://tactiboard.com/share/abc123


---

## RF-015 — Visualizar prancheta pública

Uma pessoa com o link deve conseguir visualizar a análise.

Regras:

- Não deve ser necessário possuir conta.
- Usuários externos não podem editar.
- Apenas visualização.

---

# 10. Autorização

## RN-001 — Propriedade das pranchetas

Cada prancheta possui um proprietário.

Somente o proprietário pode:

- Editar.
- Excluir.
- Alterar configurações.
- Gerar links de compartilhamento.

---

## RN-002 — Privacidade

Pranchetas privadas:

- Somente o dono acessa.

Pranchetas compartilhadas:

- Qualquer pessoa com o link pode visualizar.

---

# 11. Critérios de Aceitação do MVP

O MVP será considerado funcional quando:

## Usuário

✓ Conseguir criar conta.

✓ Conseguir fazer login.

✓ Conseguir acessar sua área privada.


## Pranchetas

✓ Conseguir criar uma análise.

✓ Conseguir editar informações.

✓ Conseguir excluir.

✓ Conseguir visualizar suas análises.


## Editor

✓ Conseguir visualizar um campo.

✓ Conseguir adicionar jogadores.

✓ Conseguir movimentar elementos.

✓ Conseguir adicionar elementos auxiliares.

✓ Conseguir salvar e recuperar uma análise.


## Compartilhamento

✓ Conseguir gerar link público.

✓ Usuário externo conseguir visualizar.

---

# 12. Considerações para Evolução

A arquitetura do MVP deve permitir futuras extensões:

- Histórico de versões.
- Times e organizações.
- Múltiplos usuários por equipe.
- Permissões avançadas.
- Biblioteca de jogadas.
- Animações.
- Colaboração em tempo real.
- Integrações externas.

Porém essas funcionalidades não devem influenciar a complexidade inicial do MVP.
