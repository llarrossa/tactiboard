# TactiBoard — Documentação de Funcionalidades

## 1. Objetivo do Documento

Este documento descreve **o que o TactiBoard faz hoje**, do ponto de vista de
quem usa o produto.

Ele complementa os demais:

| Documento | Responde |
|---|---|
| `02-mvp-requirements.md` | O que o produto **deve** fazer (RF-001 a RF-015) |
| `05-development-roadmap.md` | **Quando** cada parte foi construída |
| **Este documento** | O que existe **agora**, como se usa e onde vive no código |

Escrito na Fase 6 (2026-08-06), com o MVP completo — fases 0 a 5 concluídas.

---

# 2. Visão Geral do Fluxo

```
Cadastro / login
      ↓
Dashboard — lista das próprias pranchetas
      ↓
Nova prancheta (nome, descrição, categoria)
      ↓
Editor tático — campo, peças, arrastar, salvar
      ↓
Compartilhar → link público /share/{token}
      ↓
Qualquer pessoa visualiza, sem conta e sem poder editar
```

---

# 3. Contas e Acesso

## 3.1 Cadastro, login e logout (RF-001, RF-002, RF-003)

- Cadastro com nome, e-mail, senha e confirmação. O e-mail é único.
- A senha é gravada com hash — em nenhum ponto ela existe em texto puro.
- Login por e-mail e senha, com a opção "lembrar-me".
- Logout encerra a sessão e devolve o visitante à página inicial.
- **Cinco tentativas erradas bloqueiam o login** daquele e-mail naquele
  endereço por um minuto. A mensagem informa quantos segundos faltam. O
  bloqueio é por e-mail + IP: uma conta sob ataque não derruba o login das
  demais.

## 3.2 Recuperação de senha

Fluxo padrão por e-mail: o usuário pede o link, recebe a mensagem e define a
senha nova. Em desenvolvimento o `MAIL_MAILER=log` grava a mensagem em
`storage/logs`, em vez de enviá-la.

## 3.3 Perfil

- Alterar nome e e-mail.
- Alterar a senha, informando a senha atual.
- Excluir a conta, confirmando com a senha. **A exclusão leva junto todas as
  pranchetas e todos os links compartilhados** — não há lixeira nem desfazer.

## 3.4 Verificação de e-mail

As telas e rotas existem, herdadas do Breeze, mas o recurso está **inativo**: o
model `User` não implementa `MustVerifyEmail`. Está fora do MVP (`docs/02`
RF-001) e ligá-lo é implementar a interface no model.

---

# 4. Dashboard (RF-004, RF-006)

A tela inicial de quem está autenticado é a lista das próprias pranchetas.

- Cada cartão mostra **nome, categoria, data de criação e última atualização**.
- Ordenação da mais recente para a mais antiga, **12 por página**.
- Botão para criar uma prancheta nova.
- Quem ainda não tem nenhuma vê um convite para criar a primeira.
- **Prancheta de outra pessoa nunca aparece**: a consulta parte do usuário
  autenticado, não da tabela inteira.

Não existe uma rota `/boards` de índice — o dashboard *é* a listagem.

---

# 5. Pranchetas (RF-005, RF-007, RF-008)

## 5.1 Criar

Formulário com três campos:

| Campo | Regra |
|---|---|
| Nome | Obrigatório, até 255 caracteres |
| Descrição | Opcional |
| Categoria | Uma das cinco abaixo |

Categorias: **Ataque**, **Defesa**, **Bola parada**, **Treinamento** e
**Outros**. Elas são gravadas em inglês (`attack`, `defense`, `set_piece`,
`training`, `other`) e exibidas em português.

A prancheta nasce **privada** e com o campo vazio. Torná-la pública é assunto do
compartilhamento (§7).

## 5.2 Editar e excluir

- `boards.edit` altera nome, descrição e categoria.
- O conteúdo visual é editado no próprio editor (§6), não neste formulário.
- A exclusão pede confirmação e é **definitiva**: não há Soft Delete no MVP. Os
  links compartilhados da prancheta caem junto, e a URL pública deixa de abrir.

## 5.3 Propriedade (RN-001)

Somente o dono visualiza, edita, exclui e compartilha. Tentar acessar a
prancheta de outra pessoa na área autenticada responde **403**.

---

# 6. Editor Tático (RF-009 a RF-013)

O editor é a própria tela da prancheta (`boards.show`). A prancheta *é* o campo.

## 6.1 O campo (RF-009)

Campo em SVG com as medidas oficiais da IFAB (105 m × 68 m): linhas laterais e
de fundo, meio-campo com círculo central, grandes e pequenas áreas, marcas de
pênalti, arcos e traves.

As posições são gravadas em **coordenadas do campo** (`0..1050` × `0..680`, em
decímetros), não em pixels: a mesma prancheta abre igual no notebook e no
celular.

## 6.2 As peças (RF-010, RF-012)

| Peça | O que guarda |
|---|---|
| Jogador do próprio time | Número (1 a 99) e posição |
| Jogador adversário | O mesmo, em outra cor |
| Bola | Posição |
| Cone | Posição |
| Texto | Conteúdo (até 120 caracteres) e posição |
| Seta | Ponto inicial e ponto final |

O jogador novo recebe **o menor número livre do seu lado**, então em geral não é
preciso digitar número nenhum. A prancheta comporta **100 elementos**; a barra
mostra o total (`n/100` ) e desabilita os botões ao chegar no limite.

## 6.3 Manipulação (RF-011)

- **Arrastar** com o mouse ou com o dedo. A peça acompanha o ponteiro e para na
  linha se for levada para fora.
- **Arrastar a seta inteira** move as duas pontas juntas, preservando o
  comprimento; arrastar uma ponta muda direção e tamanho.
- **Clicar** seleciona e abre o painel de propriedades, onde dá para trocar o
  número e o lado do jogador, o texto da observação, **duplicar** e **remover**.
- **Duplicar** cria a cópia deslocada — e, se for jogador, com o próximo número
  livre, para não existirem dois iguais em campo.
- **Limpar campo** esvazia tudo, com confirmação. Como toda edição, só vira
  gravação ao salvar.

## 6.4 Teclado e acessibilidade

Cada peça é alcançável por `Tab`, tem rótulo próprio para leitor de tela e é
selecionada com `Enter` ou espaço. O foco não seleciona sozinho.

| Atalho | Ação |
|---|---|
| `Ctrl`/`⌘` + `S` | Salvar a prancheta |
| `Ctrl`/`⌘` + `D` | Duplicar o elemento selecionado |
| `Delete` ou `Backspace` | Remover o elemento selecionado |
| `Esc` | Limpar a seleção |
| `←` `↑` `↓` `→` | Mover a peça (1 metro por toque) |
| `Shift` + setas | Mover em passo fino (10 cm) |
| `Tab` e `Enter` | Alcançar uma peça e selecioná-la |

Nenhum atalho faz algo que a interface não ofereça por botão, e nenhum dispara
enquanto o usuário digita em um campo ou com um modal aberto. A lista fica na
própria tela, em "Atalhos de teclado".

## 6.5 Salvar (RF-013)

- O botão **Salvar prancheta** grava o campo inteiro em `canvas_data`.
- Enquanto houver edição por gravar, o editor mostra **"Alterações não salvas"**
  e o navegador pede confirmação se a aba for fechada.
- O editor **não salva sozinho** e **não tem desfazer**: enquanto a prancheta
  não é salva, recarregar a página traz a última versão gravada de volta — é o
  caminho de arrependimento.

## 6.6 No celular

O campo se ajusta à tela e o painel de propriedades empilha abaixo dele.
Arrastar uma peça move a peça; o dedo sobre a grama rola a página normalmente.

---

# 7. Compartilhamento (RF-014, RF-015)

## 7.1 Gerar o link

No painel **Compartilhamento**, dentro da prancheta. Um clique faz duas coisas
que, para o usuário, são uma só: gera o token e torna a prancheta pública. O
endereço tem a forma `https://…/share/{token}`, com 32 caracteres aleatórios.

O painel mostra a URL em um campo de texto, com botão **Copiar link**.

## 7.2 A página pública

Quem abre o link vê o nome da prancheta, a categoria, a descrição, o campo com a
jogada desenhada e a data da última atualização.

- **Não precisa de conta.**
- **Não dá para editar**: a página é somente leitura, sem toolbar e sem editor.
- A página pede aos buscadores que **não a indexem** (`noindex, nofollow`).

## 7.3 Revogar e gerar um endereço novo

| Ação | O que faz |
|---|---|
| **Deixar de compartilhar** | Torna a prancheta privada. O link para de abrir, mas não é apagado — voltar a compartilhar devolve a mesma URL |
| **Gerar um link novo** | Troca o token. O endereço anterior deixa de abrir na hora, e a visibilidade não muda |

Use *deixar de compartilhar* para fechar o acesso; use *gerar um link novo*
quando o endereço tiver vazado e a prancheta precisar continuar pública.

## 7.4 Acesso negado

Qualquer falha — token inexistente, prancheta privada, link expirado, prancheta
excluída — responde **404**, nunca 403. O token é o segredo: distinguir os
motivos confirmaria a alguém que ele acertou um token válido.

---

# 8. Limites e Limitações Conhecidas

| Item | Limite |
|---|---|
| Elementos por prancheta | 100 |
| Caracteres em um texto | 120 |
| Número do jogador | 1 a 99 |
| Pranchetas por página no dashboard | 12 |
| Tentativas de login | 5, depois um minuto de espera |

Limitações registradas, todas deliberadas no MVP:

1. **Não existe desfazer.** Limpar o campo e remover elemento só se recuperam
   recarregando a página antes de salvar.
2. **O aviso de alteração pendente é conservador.** Ele não volta atrás quando o
   canvas retorna sozinho ao estado gravado — avisar à toa custa uma
   confirmação; deixar de avisar custa o trabalho do usuário.
3. **A expiração de link existe no banco, não na interface.** A coluna
   `expires_at` é respeitada, mas nenhuma tela define prazo.
4. **Um link ativo por prancheta.**
5. **Mover pelo teclado custa uma requisição por tecla.** Aceitável no uso real,
   já que o Livewire agrupa chamadas próximas.
6. **Sem histórico de versões, colaboração, animação, vídeo ou IA** — tudo isso
   é Fase 7 (`docs/05` §10) e está fora do MVP por decisão (`docs/02` §3).

---

# 9. Onde Cada Coisa Vive

| Funcionalidade | Pontos principais |
|---|---|
| Autenticação | `routes/auth.php`, `app/Http/Controllers/Auth/`, `RegisterUserAction` |
| Dashboard | `DashboardController`, `resources/views/dashboard.blade.php` |
| CRUD de pranchetas | `BoardController`, `Create/Update/DeleteBoardAction`, `BoardPolicy` |
| Editor | `app/Livewire/BoardEditor.php`, `resources/views/livewire/board-editor.blade.php`, `resources/views/components/canvas/` |
| Regras do canvas | `app/Rules/CanvasRules.php` (ponto único), schema em `docs/03` §6.1 |
| Arrasto e atalhos | `resources/js/app.js` (`tactiboardCanvasDrag`, `tactiboardEditorShortcuts`) |
| Compartilhamento | `SharedLinkController` (dono), `SharedBoardController` (anônimo), `Generate/Revoke/RotateSharedLinkAction`, `SharedLink::accessible()` |

Os porquês de cada decisão estão em `docs/04` (arquitetura) e no histórico de
fases em `docs/05`.
