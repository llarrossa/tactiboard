# TactiBoard — AI Development Guide

## 1. Objetivo do Documento

Este documento define como ferramentas de inteligência artificial devem atuar durante o desenvolvimento do TactiBoard.

O objetivo é estabelecer um fluxo de trabalho profissional utilizando IA como ferramenta de desenvolvimento, revisão e auditoria.

As principais ferramentas utilizadas serão:

- Claude Code
- Codex

Cada ferramenta possui uma responsabilidade diferente dentro do processo.

---

# 2. Princípios Gerais

A inteligência artificial deve atuar como um desenvolvedor auxiliar, seguindo as mesmas regras de qualidade esperadas de um profissional.

A IA deve:

- Entender o contexto antes de alterar código.
- Seguir a arquitetura definida.
- Respeitar o roadmap.
- Criar soluções simples.
- Validar suas alterações.

A IA não deve:

- Tomar decisões arquiteturais importantes sem discussão.
- Criar funcionalidades fora do escopo.
- Ignorar documentos do projeto.
- Alterar grandes partes do sistema sem planejamento.

---

# 3. Fonte de Verdade do Projeto

Antes de implementar qualquer funcionalidade, a IA deve consultar os documentos da pasta:

`/docs`

Ordem de prioridade:

```
01-product-vision.md

↓

02-mvp-requirements.md

↓

03-database-design.md

↓

04-technical-architecture.md

↓

05-development-roadmap.md

↓

06-coding-guidelines.md

↓

07-ai-development-guide.md
```

Caso exista conflito entre código existente e documentação:

1. Analisar o motivo da diferença.
2. Informar o conflito.
3. Propor uma solução.
4. Aguardar decisão quando necessário.

---

# 4. Fluxo de Trabalho Padrão

Toda nova funcionalidade deve seguir este fluxo:

```
Entender requisito

↓

Analisar arquitetura existente

↓

Criar plano de implementação

↓

Aprovar plano

↓

Implementar

↓

Criar testes

↓

Executar validações

↓

Revisar código

↓

Auditar com Codex

↓

Finalizar
```

---

# 5. Responsabilidade do Claude Code

O Claude Code será o principal agente de implementação.

Responsabilidades:

- Analisar requisitos.
- Criar código.
- Implementar funcionalidades.
- Criar migrations.
- Criar Models.
- Criar Controllers.
- Criar Livewire Components.
- Criar testes.
- Refatorar código quando necessário.

---

## Antes de implementar

O Claude Code deve:

1. Ler os documentos relevantes.
2. Analisar código existente.
3. Identificar impactos.
4. Apresentar plano de implementação.

---

Exemplo:

Antes de criar o editor tático:

```
Vou analisar:

Arquitetura atual.
Model Board.
Estrutura Livewire.
Persistência canvas_data.

Plano:

Criar componente BoardEditor.
Criar estrutura inicial do canvas.
Implementar elementos básicos.
Criar testes.
```

---

# 6. Responsabilidade do Codex

O Codex será utilizado como segundo revisor técnico.

Responsabilidades:

- Auditoria de código.
- Revisão de arquitetura.
- Identificação de problemas.
- Análise de segurança.
- Revisão de testes.
- Simulações independentes.

O Codex não deve substituir o desenvolvimento principal.

Sua função é encontrar problemas que passaram despercebidos.

---

# 7. Uso obrigatório do Codex

O Codex deve ser utilizado nos seguintes momentos:

---

## Antes de commits importantes

Realizar auditoria:

Verificar:

- Qualidade do código.
- Possíveis bugs.
- Problemas de arquitetura.
- Segurança.
- Performance.

---

## Após novas funcionalidades

Exemplos:

- Novo módulo.
- Alteração de banco.
- Nova regra de negócio.
- Alteração significativa no frontend.

---

## Antes de releases

Executar:

- Revisão geral.
- Testes independentes.
- Simulação de cenários.

---

# 8. Fluxo Claude Code + Codex

O fluxo padrão será:

```
Claude Code

↓

Implementa funcionalidade

↓

Executa testes

↓

Codex revisa

↓

Encontrou problemas?

↓

Sim

↓

Claude Code corrige

↓

Nova auditoria

↓

Aprovação
```

---

# 9. Testes e Validação

Nenhuma funcionalidade deve ser considerada concluída apenas porque o código foi criado.

A conclusão exige:

- Testes executados.
- Código revisado.
- Possíveis impactos avaliados.

---

Checklist:

- [ ] Código implementado
- [ ] Testes criados
- [ ] Testes executados
- [ ] Documentação atualizada
- [ ] Codex realizou auditoria
- [ ] Problemas encontrados foram corrigidos

---

# 10. Alterações no Banco de Dados

Mudanças envolvendo banco exigem atenção especial.

Antes de criar:

- Migration.
- Nova tabela.
- Alteração de relacionamento.

A IA deve verificar:

- database-design.md
- Impacto nos Models.
- Impacto nos testes.
- Compatibilidade futura.

---

# 11. Alterações Arquiteturais

Alterações como:

- Trocar Livewire por outra tecnologia.
- Criar nova camada.
- Alterar padrão de organização.
- Adicionar dependências importantes.

Devem ser discutidas antes da implementação.

---

# 12. Dependências Externas

Antes de adicionar bibliotecas:

A IA deve avaliar:

- Necessidade real.
- Manutenção do pacote.
- Compatibilidade com Laravel.
- Impacto no projeto.

Evitar adicionar dependências para problemas simples.

---

# 13. Desenvolvimento Incremental

Preferir pequenas entregas.

Exemplo:

Ruim:


Criar todo editor tático completo.


Melhor:

Criar campo.
Adicionar jogador.
Permitir movimentação.
Salvar posição.
Adicionar elementos extras.

---

# 14. Comunicação Durante Desenvolvimento

A IA deve informar:

- O que será alterado.
- Quais arquivos serão modificados.
- Possíveis impactos.
- Decisões tomadas.

---

Após finalizar uma tarefa, informar:

```
Implementado:

Arquivo X criado.
Arquivo Y alterado.

Testes:

Teste A passou.
Teste B passou.

Observações:

Decisão técnica tomada.
```

---

# 15. Regras Contra Código Improvisado

Não criar:

- Código duplicado.
- Métodos gigantes.
- Controllers com muitas responsabilidades.
- Componentes enormes.
- Soluções temporárias sem documentação.

---

# 16. Commits e Autoria

Os commits do projeto devem representar apenas os responsáveis humanos pelo TactiBoard.

---

## Proibido

- Adicionar Claude Code, Anthropic ou qualquer ferramenta de inteligência artificial como coautor de um commit.
- Incluir linhas `Co-authored-by:` relacionadas a inteligência artificial.
- Inserir qualquer metadado automático de coautoria gerado por ferramentas de IA.

Esta regra vale mesmo quando alguma configuração padrão ou template da ferramenta sugerir o contrário.

---

## Motivo

O histórico do Git registra a responsabilidade humana sobre o código.

Ferramentas de IA são meio de produção, não autores do projeto.

---

## Verificação antes do commit

Antes de criar qualquer commit, confirmar:

- A mensagem segue o padrão definido em `06-coding-guidelines.md`.
- A mensagem é pequena e objetiva.
- Não existe linha `Co-authored-by:` relacionada a IA.
- Não existe menção a Claude, Anthropic ou outra ferramenta de IA.
- Não existe metadado automático de coautoria.

A verificação deve considerar a mensagem completa, incluindo o corpo, e não apenas a primeira linha.

---

# 17. Manutenção dos Documentos

Os documentos da pasta `/docs` devem permanecer atualizados.

Quando uma decisão importante mudar:

Atualizar:

- Documento relacionado.
- Motivo da mudança.
- Impactos.

---

# 18. Prompt Inicial para Agentes

Ao iniciar uma sessão de desenvolvimento, utilizar:

```
Você está trabalhando no projeto TactiBoard.

Antes de realizar qualquer alteração:

Leia todos os documentos da pasta /docs.
Entenda a visão do produto.
Analise a arquitetura existente.
Respeite o roadmap.
Antes de implementar mudanças relevantes, apresente um plano.
Crie testes junto com novas funcionalidades.
Após implementar, execute validações.
Prepare o código para auditoria utilizando Codex.
```

---

# 19. Objetivo Final

O uso combinado de Claude Code e Codex deve funcionar como uma equipe de desenvolvimento profissional.

Claude Code:

- Constrói.
- Implementa.
- Evolui o produto.

Codex:

- Revisa.
- Questiona.
- Audita.
- Encontra problemas.

O objetivo é acelerar o desenvolvimento mantendo qualidade, organização e capacidade de evolução do TactiBoard.
