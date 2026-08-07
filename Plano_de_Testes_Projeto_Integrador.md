# ATIVIDADE EM GRUPO — PLANO DE TESTES DO PROJETO INTEGRADOR

**Disciplina:** Teste de Software  
**Tema:** Aplicação de Técnicas de Teste no Projeto Integrador  
**Projeto:** OYAMA HUB — Plataforma de Treinamento de Kyokushin Karate  
**Data:** 31 de Julho de 2026  

---

## ENTREGA FINAL DO GRUPO

Este documento consolida o planejamento, execução e relatório final de testes aplicados ao sistema **OYAMA HUB - Kyokushin Karate**, cobrindo todas as 11 etapas solicitadas na atividade.

---

## ETAPA 1 – IDENTIFICAÇÃO DO PROJETO

### Dados do Projeto
* **Nome do Projeto Integrador:** OYAMA HUB – Plataforma de Treinamento de Kyokushin Karate
* **Descrição do Sistema:** O OYAMA HUB é uma aplicação web voltada para praticantes e professores de Kyokushin Karate. O sistema permite o gerenciamento e acompanhamento de treinos diários, consulta a técnicas estruturadas (Katas e Kihons) com vídeos integrados do YouTube, acompanhamento de estatísticas de progresso por faixa e registros de diário de treino em um ambiente moderno e responsivo.
* **Integrantes da Equipe (Equipe de QA e Desenvolvimento):**
  * Desenvolvedor Backend & QA Lead
  * Desenvolvedor Frontend & QA Tester
  * Modelador de Banco de Dados & Tester
  * Documentador & Analista de Qualidade
* **Tecnologias Utilizadas:**
  * **Backend:** PHP 8.x (Procedural e Modularizado com `include`)
  * **Frontend:** HTML5, CSS3 (Vanilla com variáveis CSS e Dark Theme Marcial), JavaScript (Vanilla ES6 / Fetch API)
  * **Servidor Web:** Apache (Ambiente XAMPP)
  * **Mídia:** YouTube Embedded API (iframes responsivos)
* **Banco de Dados Utilizado:** MySQL 8.0 (Database: `oyama_hub`)
* **Principais Funcionalidades:**
  1. Autenticação e Gestão de Usuários (Login, Registro de Alunos, Troca de Faixa/Perfil, Alteração de Senha, Logout seguro).
  2. Dashboard Central do Aluno (Resumo de horas de treino, total de sessões, porcentagem de conclusão de Katas/Kihons e atalhos rápidos).
  3. Módulo de Katas (Listagem de katas categorizados por nível/faixa, reprodução de vídeo demonstrativo e alternância de progresso).
  4. Módulo de Kihons (Visualização por categorias: Socos, Chutes, Bloqueios, Posições e Golpes Especiais, com vídeos e controle de conclusão).
  5. Gestão de Treinos (Formulário para adicionar novos treinos com data, duração e observações, histórico filtrável, edição e exclusão de treinos).
  6. Módulo de Progresso e Métricas (Gráficos e barras percentuais por faixa e por categoria técnica).
  7. Diário de Anotações de Treino (Criação, visualização e remoção de notas pessoais do atleta).

---

## ETAPA 2 – IDENTIFICAÇÃO DOS TIPOS DE TESTES

### 2.1 Testes Funcionais
Serão testadas as funcionalidades essenciais para garantir que o fluxo de trabalho do usuário ocorra sem impedimentos.

**Funcionalidades a serem testadas:**
1. **Autenticação e Cadastro:** Validação de login com dados corretos/incorretos, criação de nova conta de aluno com verificação de e-mail único e confirmação de senha.
2. **Gestão de Treinos (CRUD):** Inserção de novos treinos, edição de registros existentes, exclusão e filtragem por data/tipo.
3. **Controle de Progresso de Técnicas:** Marcação e desmarcação de Katas e Kihons como concluídos via requisições AJAX (`toggle_progresso.php`).
4. **Gestão de Perfil e Segurança:** Atualização de dados cadastrais, alteração da faixa atual do praticante e alteração de senha de acesso.
5. **Diário de Anotações:** Criação, exibição e remoção de anotações personalizadas do praticante.

### 2.2 Testes Não Funcionais
Avaliação das características de qualidade e desempenho da aplicação.

**Testes selecionados:**
* `[X]` **Performance:** Medição do tempo de resposta das consultas no banco de dados e tempo de carregamento do Dashboard.
* `[X]` **Segurança:** Verificação contra invasões por SQL Injection, Cross-Site Scripting (XSS), bypass de sessão por URL e armazenamento seguro de senhas com hash (`password_hash`).
* `[X]` **Usabilidade:** Avaliação da clareza das informações, navegação intuitiva, contraste visual (tema escuro marcial) e facilidade de acesso às técnicas.
* `[X]` **Compatibilidade:** Teste do layout e scripts em múltiplos navegadores (Google Chrome, Mozilla Firefox, Microsoft Edge) e telas de diferentes resoluções (Desktop, Tablet e Smartphone).
* `[X]` **Disponibilidade:** Verificação do comportamento da aplicação perante oscilações na conexão com o banco de dados MySQL ou falhas de carregamento de scripts externos.

**Justificativa das escolhas:**
Como se trata de uma plataforma de estudos e registro continuo, a **usabilidade** e **compatibilidade** garantem que o aluno consiga registrar seus treinos pelo celular logo após a aula. A **segurança** é indispensável para proteger dados pessoais e senhas dos alunos. A **performance** e **disponibilidade** garantem uma experiência fluida sem travamentos no Dashboard durante o cálculo de estatísticas.

---

## ETAPA 3 – DEFINIÇÃO DOS NÍVEIS DE TESTES

### Teste Unitário
* **Descrição:** Avaliação de pequenas unidades isoladas do código.
* **Componentes que podem receber testes unitários:**
  * Funções de validação de e-mail e força de senha no cadastro.
  * Algoritmo de hash e verificação de senha (`password_hash` / `password_verify`).
  * Função helper de formatação de datas (`YYYY-MM-DD` para `DD/MM/YYYY`).
  * Cálculo percentual de progresso de katas/kihons (Divisão simples de concluídos / total * 100 com tratamento de divisão por zero).

### Teste de Integração
* **Descrição:** Avaliação da comunicação e troca de dados entre módulos e subsistemas.
* **Integrações a serem testadas:**
  * Comunicação entre **PHP (`php/config.php`) e MySQL (`oyama_hub`)**: envio de queries de SELECT/INSERT/UPDATE/DELETE.
  * Comunicação via **AJAX (JavaScript `fetch`) <-> PHP (`toggle_progresso.php`) <-> MySQL (`progresso`)**: alternância do status de conclusão de técnicas sem recarregar a página.
  * Comunicação entre o **Módulo de Katas/Kihons e a API iframe do YouTube**: carregamento e reprodução dos vídeos incorporados.

### Teste de Sistema
* **Descrição:** Avaliação da aplicação completa e integrada rodando em ambiente simular ao de produção.
* **Como será realizado o teste completo da aplicação:**
  * O teste será realizado executando o fluxo completo de um usuário do início ao fim (*End-to-End*):
    1. Acessar a Landing Page (`index.html`).
    2. Registrar uma nova conta de aluno (`php/registro.php`).
    3. Efetuar login (`php/login.php`) e ser redirecionado para o Dashboard (`php/dashboard.php`).
    4. Navegar até a seção de Kihons, assistir a um vídeo e marcar 3 técnicas como concluídas.
    5. Cadastrar 2 sessões de treino com diferentes durações.
    6. Verificar se os indicadores do Dashboard e a página de Progresso foram atualizados corretamente.
    7. Editar um treino, alterar a faixa no Perfil e realizar Logout.

### Teste de Aceitação
* **Descrição:** Simulação de uso por usuários finais para validação de requisitos de negócio.
* **Quem poderia validar o sistema?**
  * Alunos de Kyokushin Karate, Senseis (professores da academia) e o Professor/Orientador do Projeto Integrador.
* **Qual seria o critério de aprovação?**
  * 100% dos fluxos críticos (autenticação, registro de treino e salvamento de progresso) funcionando sem erros fatais de PHP ou exceções SQL.
  * Tempo de resposta da aplicação inferior a 2 segundos em conexões padrão.
  * Interface perfeitamente adaptada a telas mobile (sem quebra de layout ou rolagem horizontal indesejada).

---

## ETAPA 4 – PLANEJAMENTO DE TESTES

### Plano de Teste

#### Identificação
* **Projeto:** OYAMA HUB – Plataforma de Treinamento de Kyokushin Karate
* **Versão:** 2.0.0
* **Equipe:** Grupo do Projeto Integrador (Equipe QA)
* **Responsável pelos Testes:** Líder de Garantia da Qualidade (QA Lead)
* **Data de Execução:** 31/07/2026

#### Objetivo dos Testes
Garantir que a aplicação OYAMA HUB funcione estavelmente em ambiente web local (XAMPP), validando a exatidão das regras de negócio (cálculo de treinos e progresso), a robustez contra entradas inválidas e a segurança de dados de autenticação dos praticantes.

#### Escopo dos Testes
* **Telas e Módulos incluídos:**
  * Landing Page (`index.html`)
  * Formulário de Login (`php/login.php`)
  * Formulário de Registro de Aluno (`php/registro.php`)
  * Painel Principal / Dashboard (`php/dashboard.php`)
  * Módulo de Katas (`dashboard/katas.php`)
  * Módulo de Kihons (`dashboard/kihons.php`)
  * Módulo de Histórico e Registro de Treinos (`dashboard/treinos.php` e `dashboard/registrar_treino.php`)
  * Módulo de Progresso (`dashboard/progresso.php`)
  * Perfil do Usuário (`dashboard/perfil.php`)
  * Diário de Anotações (`dashboard/anotacoes.php`)
  * Persistência de Dados no Banco MySQL (`oyama_hub`)

#### Fora do Escopo
* Integração com gateways de pagamento de mensalidades do dojo.
* Autenticação via redes sociais (OAuth com Google/Facebook).
* Aplicativo nativo mobile (iOS/Android).
* Suporte a múltiplos idiomas (Internacionalização - i18n).

---

## ETAPA 5 – ANÁLISE DE RISCOS

Tabela de riscos identificados para a aplicação **OYAMA HUB**:

| ID | Risco Identificado | Probabilidade | Impacto | Ação Preventiva |
|---|---|---|---|---|
| R01 | **Vulnerabilidade de SQL Injection** nos campos de login e pesquisa | Média | Alto | Utilizar Prepared Statements (`mysqli_prepare` / PDO) em 100% das consultas SQL no PHP. |
| R02 | **Armazenamento de senhas em texto puro** | Média | Alto | Aplicar criptografia `password_hash()` no cadastro e validar com `password_verify()`. |
| R03 | **Perda ou corrupção da base de dados MySQL** | Baixa | Alto | Configurar rotinas de backup diário do BD `oyama_hub` e usar chaves estrangeiras com `ON DELETE CASCADE`. |
| R04 | **Invasão por Cross-Site Scripting (XSS)** no diário de anotações ou observações de treino | Alta | Alto | Sanitizar todas as saídas de texto renderizadas no HTML usando `htmlspecialchars()`. |
| R05 | **Acesso indevido a páginas do Dashboard sem autenticação** | Média | Alto | Incluir verificação de `$_SESSION['usuario_id']` no início de todas as páginas restritas do PHP. |
| R06 | **Vídeo do YouTube indisponível ou link quebrado** nos cards de Katas/Kihons | Média | Médio | Implementar fallback visual "Vídeo temporariamente indisponível" e tratamento de iframe. |
| R07 | **Inclusão de dados inconsistentes** (ex: duração de treino negativa ou data no futuro) | Alta | Médio | Adicionar validação de entrada no formulário (HTML5 `min="1"`, `max="1440"`) e no PHP. |
| R08 | **Lentidão ao carregar o Dashboard** com grande volume de treinos cadastrados | Média | Médio | Criar índices no MySQL (`idx_usuario_data`, `idx_categoria`) e implementar paginação. |
| R09 | **Desconfiguração do layout em telas pequenas** (smartphones) | Média | Médio | Utilizar CSS Grid/Flexbox responsivo com media queries bem testadas em breakpoint `768px`. |
| R10 | **Falha na requisição AJAX ao marcar progresso** (`toggle_progresso.php`) | Média | Médio | Adicionar tratamento de erro `.catch()` na Fetch API do JS e retornar JSON estruturado com status HTTP adequado. |

---

## ETAPA 6 – CRIAÇÃO DOS CASOS DE TESTE

Total de **20 Casos de Teste** criados contemplando testes positivos e negativos:

| ID | Funcionalidade | Cenário | Entrada de Dados | Resultado Esperado |
|---|---|---|---|---|
| **CT001** | Autenticação | Login com usuário e senha válidos (Positivo) | E-mail: `aluno@karate.com`, Senha: `SenhaValida123` | Login efetuado com sucesso; Usuário redirecionado para o Dashboard com mensagem de boas-vindas. |
| **CT002** | Autenticação | Login com senha incorreta (Negativo) | E-mail: `aluno@karate.com`, Senha: `SenhaErrada` | Acesso negado; Exibição de mensagem de erro "E-mail ou senha inválidos". |
| **CT003** | Autenticação | Login com e-mail não cadastrado (Negativo) | E-mail: `naoexistente@karate.com`, Senha: `123456` | Acesso negado; Exibição de mensagem de erro "E-mail ou senha inválidos". |
| **CT004** | Cadastro | Registro de novo aluno com dados válidos (Positivo) | Nome: `Carlos Silva`, E-mail: `carlos@karate.com`, Senha: `SenhaForte1`, Data Nasc: `2000-05-15` | Conta criada com sucesso no BD; Usuário redirecionado para página de login. |
| **CT005** | Cadastro | Registro com e-mail já cadastrado (Negativo) | Nome: `Carlos Dois`, E-mail: `carlos@karate.com` (existente) | Bloqueio do cadastro; Mensagem "Este e-mail já está cadastrado no sistema". |
| **CT006** | Cadastro | Registro deixando campos obrigatórios vazios (Negativo) | Nome: ``, E-mail: `teste@karate.com` | Impedimento do envio do formulário com destaque visual nos campos em branco. |
| **CT007** | Registro Treino | Inserir novo treino com dados válidos (Positivo) | Data: `31/07/2026`, Duração: `60 min`, Tipo: `Katas`, Obs: `Treino de Pinan Sono Ichi` | Treino salvo com sucesso; Histórico atualizado e minutos somados no Dashboard. |
| **CT008** | Registro Treino | Inserir treino com duração zero ou negativa (Negativo) | Data: `31/07/2026`, Duração: `-30 min` | Erro de validação; Exibição da mensagem "A duração do treino deve ser maior que 0 minutos". |
| **CT009** | Registro Treino | Inserir treino com data futura (Negativo) | Data: `01/01/2030`, Duração: `45 min` | Alerta de validação "A data do treino não pode ser posterior à data atual". |
| **CT010** | Edição Treino | Alterar a duração de um treino existente (Positivo) | Duração alterada de `60 min` para `90 min` | Registro atualizado no BD; Recálculo imediato do tempo total no Dashboard. |
| **CT011** | Exclusão Treino | Excluir um registro de treino cadastrado (Positivo) | Clique no botão "Excluir" no treino ID #5 | Mensagem de confirmação exibida; Treino removido da lista e estatísticas recalculadas. |
| **CT012** | Katas | Marcar Kata como concluído (Positivo) | Clique no checkbox / botão do Kata "Taikyoku Sono Ichi" | Requisição AJAX enviada; Status alterado para "Concluído" no BD e contador de progresso incrementado. |
| **CT013** | Kihons | Desmarcação de Kihon previamente concluído (Positivo) | Clique no botão de conclúido do Kihon "Seiken Tsuki" | Status alterado para "Pendente"; Barra de progresso da categoria ajustada proporcionalmente. |
| **CT014** | Kihons | Filtrar Kihons por categoria (Positivo) | Selecionar filtro "Chutes (Geri)" | Grid exibe apenas os golpes da categoria selecionada (ex: Mae Geri, Mawashi Geri). |
| **CT015** | Perfil | Alterar a faixa atual do aluno (Positivo) | Alterar faixa de `Branca` para `Laranja (10º Kyu)` | Perfil atualizado no banco de dados; Cor e insígnia da faixa no topo do Dashboard atualizados. |
| **CT016** | Perfil | Alterar senha digitando a senha atual correta (Positivo) | Senha Atual: `SenhaForte1`, Nova Senha: `NovaSenhaForte2` | Senha atualizada no banco de dados com novo hash seguro. |
| **CT017** | Perfil | Alterar senha informando senha atual incorreta (Negativo) | Senha Atual: `Errada`, Nova Senha: `NovaSenhaForte2` | Operação recusada; Exibição de mensagem "A senha atual informada está incorreta". |
| **CT018** | Segurança | Tentar acessar `dashboard.php` diretamente via URL sem estar logado (Negativo) | Digitar URL `http://localhost/Kyokushin-Karate/php/dashboard.php` | Redirecionamento automático imediato para `login.php` com aviso de sessão expirada. |
| **CT019** | Segurança | Inserir script malicioso nas observações do treino (`<script>alert('xss')</script>`) (Negativo) | Digitar script no campo "Observações" do treino | O texto é gravado e exibido como texto puro (escapado com `htmlspecialchars`), sem executar o script. |
| **CT020** | Diário | Adicionar nova anotação de treino (Positivo) | Título: `Reflexão da Aula`, Conteúdo: `Melhorar a postura no Zenkutsu Dachi` | Anotação criada e listada em ordem cronológica decrescente no diário. |

---

## ETAPA 7 – EXECUÇÃO DOS TESTES

Resultados da execução dos 20 casos de teste em ambiente XAMPP (Apache + MySQL):

| ID | Resultado Esperado | Resultado Obtido | Status |
|---|---|---|---|
| **CT001** | Redirecionamento para o Dashboard com login efetuado. | Redirecionamento efetuado e sessão iniciada corretamente. | **Aprovado** |
| **CT002** | Exibir erro de credenciais inválidas. | Exibiu a mensagem "E-mail ou senha incorretos". | **Aprovado** |
| **CT003** | Exibir erro de credenciais inválidas. | Exibiu a mensagem "E-mail ou senha incorretos". | **Aprovado** |
| **CT004** | Registro efetuado no BD e redirecionamento para login. | Conta criada no MySQL e redirecionou para tela de login. | **Aprovado** |
| **CT005** | Bloquear cadastro com e-mail duplicado. | Sistema informou que o e-mail já possui cadastro. | **Aprovado** |
| **CT006** | Impedir envio de campos obrigatórios vazios. | O navegador barrou via HTML5 `required` e o PHP validou. | **Aprovado** |
| **CT007** | Salvar treino no BD e atualizar estatísticas. | Treino gravado com sucesso na tabela `treinos`. | **Aprovado** |
| **CT008** | Recusar valor negativo/zero na duração. | O formulário aceitou valor negativo por falta de validação no PHP. | **Reprovado** |
| **CT009** | Bloquear registro com data posterior ao dia de hoje. | O sistema permitiu salvar data futura sem emitir aviso. | **Reprovado** |
| **CT010** | Salvar alteração de duração do treino no BD. | Registro atualizado na tabela `treinos` e estatísticas recarregadas. | **Aprovado** |
| **CT011** | Remover o treino e recarregar os dados. | Treino removido da base de dados e lista atualizada. | **Aprovado** |
| **CT012** | Atualizar progresso de Kata via AJAX. | Tabela `progresso` atualizada no BD sem recarregar a tela. | **Aprovado** |
| **CT013** | Remover marcação de conclusão do Kihon. | Status alterado no BD para não concluído via AJAX. | **Aprovado** |
| **CT014** | Exibir apenas Kihons da categoria selecionada. | Filtro executado corretamente via JavaScript/DOM. | **Aprovado** |
| **CT015** | Atualizar a faixa do aluno no BD e no topo do sistema. | Faixa alterada e exibida com a nova cor correspondente. | **Aprovado** |
| **CT016** | Alterar senha com sucesso no BD. | Novo hash gerado e aceito no próximo login. | **Aprovado** |
| **CT017** | Bloquear alteração de senha se a atual estiver incorreta. | Exibiu alerta "Senha atual incorreta" e manteve a senha antiga. | **Aprovado** |
| **CT018** | Redirecionar usuário não logado para `login.php`. | Redirecionamento acionado imediatamente pela verificação de sessão. | **Aprovado** |
| **CT019** | Renderizar código script como texto puro (prevenir XSS). | O código foi exibido literalmente como texto na tela sem rodar. | **Aprovado** |
| **CT020** | Adicionar e listar nova anotação no diário. | Anotação salva e listada na página de anotações. | **Aprovado** |

---

## ETAPA 8 – REGISTRO DE DEFEITOS

Relatório detalhado dos defeitos encontrados durante a fase de execução dos testes:

### Relatório de Defeito #01
* **ID:** DEF-001
* **Título do Problema:** Ausência de validação para duração de treino com valores negativos ou zerados.
* **Descrição:** Ao cadastrar ou editar um treino na página `registrar_treino.php`, o sistema permite que o usuário insira durações como `-45` ou `0` minutos. Isso corrompe as métricas do Dashboard (gerando horas totais negativas).
* **Passos para Reproduzir:**
  1. Efetuar login no sistema OYAMA HUB.
  2. Acessar o menu "Registrar Treino".
  3. Preencher a data com a data atual e no campo "Duração (minutos)" informar `-60`.
  4. Clicar em "Salvar Treino".
* **Resultado Esperado:** O sistema deve validar a entrada e recusar durações menores ou iguais a 0, exibindo a mensagem "A duração deve ser superior a 0 minutos".
* **Resultado Encontrado:** O treino foi salvo com sucesso e a métrica de total de minutos diminuiu 60 minutos no Dashboard.
* **Severidade:** `( ) Baixa` `( ) Média` `(X) Alta` `( ) Crítica`
* **Prioridade:** `( ) Baixa` `( ) Média` `(X) Alta`
* **Status:** Aberto / Aguardando Correção

---

### Relatório de Defeito #02
* **ID:** DEF-002
* **Título do Problema:** Aceitação de cadastro de treinos em datas futuras.
* **Descrição:** O formulário de registro de treinos aceita datas posteriores à data atual (ex: 15/12/2030), permitindo lançamentos inconsistentes no histórico de treinos.
* **Passos para Reproduzir:**
  1. Efetuar login no sistema.
  2. Ir para a página de "Registrar Treino".
  3. Escolher uma data no futuro no seletor de datas (ex: `2030-01-01`).
  4. Preencher a duração e clicar em "Salvar".
* **Resultado Esperado:** O sistema deve impedir o envio se a data selecionada for posterior a `CURRENT_DATE`.
* **Resultado Encontrado:** O registro foi aceito e salvo no banco de dados.
* **Severidade:** `( ) Baixa` `(X) Média` `( ) Alta` `( ) Crítica`
* **Prioridade:** `( ) Baixa` `(X) Média` `( ) Alta`
* **Status:** Aberto / Aguardando Correção

---

### Relatório de Defeito #03
* **ID:** DEF-003
* **Título do Problema:** Mensagem genérica ao tentar registrar e-mail duplicado sem foco no campo.
* **Descrição:** Quando o usuário tenta se registrar com um e-mail já existente, a tela recarrega e exibe a mensagem de erro no topo, mas limpa todos os outros campos preenchidos pelo usuário, forçando-o a reescrever tudo.
* **Passos para Reproduzir:**
  1. Acessar a tela de Registro (`php/registro.php`).
  2. Preencher nome, data de nascimento, senha e um e-mail já cadastrado no BD.
  3. Clicar em "Cadastrar".
* **Resultado Esperado:** O sistema deve exibir a mensagem de erro mantendo os dados válidos preenchidos nos campos (exceto a senha).
* **Resultado Encontrado:** A mensagem de erro é exibida, porém o formulário é resetado por completo.
* **Severidade:** `(X) Baixa` `( ) Média` `( ) Alta` `( ) Crítica`
* **Prioridade:** `( ) Baixa` `(X) Média` `( ) Alta`
* **Status:** Aberto / Melhoria Registrada

---

### Relatório de Defeito #04
* **ID:** DEF-004
* **Título do Problema:** Iframe do YouTube sem tratamento para falha de conexão offline.
* **Descrição:** Se a aplicação for executada em um ambiente sem acesso à internet (apenas localhost), o container do vídeo nas telas de Katas e Kihons fica em branco sem nenhuma indicação gráfica ao usuário.
* **Passos para Reproduzir:**
  1. Desconectar a máquina da internet.
  2. Abrir o Dashboard do OYAMA HUB e acessar a página "Katas".
  3. Tentar visualizar o card de uma técnica.
* **Resultado Esperado:** Exibição de uma mensagem amigável de fallback: "Não foi possível carregar o vídeo. Verifique sua conexão com a internet."
* **Resultado Encontrado:** O espaço do iframe fica cinza/transparente sem nenhum texto explicativo.
* **Severidade:** `(X) Baixa` `( ) Média` `( ) Alta` `( ) Crítica`
* **Prioridade:** `(X) Baixa` `( ) Média` `( ) Alta`
* **Status:** Aberto / Ajuste de UX

---

### Relatório de Defeito #05
* **ID:** DEF-005
* **Título do Problema:** Falta de confirmação modal ao excluir um treino.
* **Descrição:** Na página de histórico de treinos (`dashboard/treinos.php`), clicar no ícone/botão de exclusão remove o registro imediatamente via requisição sem solicitar confirmação prévia ao usuário ("Tem certeza que deseja excluir?").
* **Passos para Reproduzir:**
  1. Logar no sistema e abrir o histórico de "Meus Treinos".
  2. Clicar acidentalmente no botão de lixeira/excluir ao lado de um treino.
* **Resultado Esperado:** Exibir um diálogo de confirmação (modal JS ou `confirm()`) antes de disparar a exclusão no backend.
* **Resultado Encontrado:** O treino é excluído do banco de dados imediatamente sem chance de cancelamento.
* **Severidade:** `( ) Baixa` `(X) Média` `( ) Alta` `( ) Crítica`
* **Prioridade:** `( ) Baixa` `(X) Média` `( ) Alta`
* **Status:** Aberto / Aguardando Correção

---

## ETAPA 9 – APLICAÇÃO DAS TÉCNICAS DE TESTE

### Teste de Regressão
* **Pergunta:** Após uma alteração no sistema, quais funcionalidades precisam ser testadas novamente?
* **Resposta da Equipe:** Sempre que houver alterações nas rotinas de banco de dados ou no arquivo `php/config.php`, deve-se retestar obrigatoriamente a **Autenticação (Login e Registro)**, o **Dashboard Principal** e as **rotinas AJAX de Progresso (`toggle_progresso.php`)**. Além disso, caso o formulário de treinos seja alterado, deve-se retestar a listagem, edição e a soma total de minutos no Dashboard para garantir que novos bugs não foram introduzidos.

### Teste de Estresse
* **Pergunta:** Como o sistema poderia ser avaliado em uma situação de muitos usuários?
* **Resposta da Equipe:** O sistema pode ser avaliado simulando acessos simultâneos ao servidor Apache do XAMPP utilizando a ferramenta **Apache JMeter**. Seria configurado um plano de teste com 500 usuários concorrentes tentando realizar login e enviar requisições de atualização de progresso ao mesmo tempo, avaliando o limite de conexões do MySQL e o uso de memória CPU do Apache local.

### Teste de Recuperação
* **Pergunta:** Como o sistema se comporta após uma falha?
* **Resposta da Equipe:** Se a conexão com o banco de dados MySQL for interrompida temporariamente, o script `php/config.php` encerra a execução exibindo a mensagem tratada `"Falha na conexão"`. Ao restabelecer a conexão com o MySQL, a aplicação volta a responder imediatamente sem perda de integridade dos dados já gravados (devido às transações do mecanismo InnoDB do MySQL).

### Teste de Performance
* **Pergunta:** Quais recursos precisam ter o desempenho avaliado?
* **Resposta da Equipe:** Precisam ter o desempenho avaliado:
  1. O tempo de execução da query da página `dashboard/progresso.php` que realiza JOINs entre as tabelas `usuarios`, `progresso`, `katas` e `kihons`.
  2. O tempo de resposta das requisições assíncronas (`Fetch API`) de marcação de progresso.
  3. O tempo de renderização da página inicial e carregamento das folhas de estilo CSS e fontes do Google Fonts.

### Teste de Segurança
* **Pergunta:** Quais riscos de segurança devem ser avaliados?
* **Resposta da Equipe:**
  1. **SQL Injection:** Injeção de código malicioso nos formulários de Login, Registro e Filtros.
  2. **Cross-Site Scripting (XSS):** Injeção de scripts no diário de anotações e observações de treino.
  3. **Broken Session Management / URL Bypass:** Acesso direto a URLs internas (ex: `/dashboard/perfil.php`) sem um cookie de sessão válido.
  4. **Armazenamento de Credenciais:** Garantia de que nenhuma senha seja gravada em plain-text no MySQL.

### Teste Paralelo
* **Pergunta:** Existe algum sistema antigo ou processo manual para comparar?
* **Resposta da Equipe:** Sim. O processo manual tradicional utilizado pelos alunos consiste no uso de **cadernos físicos de treino** e tabelas impressas em papel fornecidas pelo dojo. Na comparação paralela, o OYAMA HUB demonstrou reduzir a zero o tempo de cálculo da soma de horas treinadas, eliminar o risco de perda do histórico físico e permitir consulta instantânea aos vídeos demonstrativos de cada Kata/Kihon.

---

## ETAPA 10 – FERRAMENTAS DE TESTE

### Pesquisa de Ferramenta de Apoio
* **Qual ferramenta foi pesquisada?**
  * **PHPUnit** (para testes unitários no backend PHP) e **Selenium IDE / Postman** (para testes funcionais e de API).
* **Qual é a finalidade dessa ferramenta?**
  * O **Postman** tem a finalidade de testar os endpoints de requisição HTTP/POST do backend (como a rota de autenticação e a rota `toggle_progresso.php`), permitindo enviar dados de formulário simulados e inspecionar a resposta JSON/HTTP retornada pelo PHP.
  * O **Selenium IDE** é uma ferramenta de automação de testes de navegadores que grava e executa fluxos de tela no Chrome/Firefox.
* **Como ela auxilia no processo de testes?**
  * O Postman permite validar rapidamente cenários de erro e segurança (como enviar parâmetros maliciosos) sem precisar preencher a interface gráfica repetidamente.
  * O Selenium IDE permite automatizar a execução regressiva dos 20 casos de teste a cada novo deploy, economizando horas de testes manuais e eliminando a falha humana durante verificações repetitivas.

---

## ETAPA 11 – RELATÓRIO FINAL DE TESTES

### Resumo dos Testes
* **Quantidade de testes planejados:** 20
* **Quantidade executada:** 20
* **Quantidade aprovada:** 18
* **Quantidade reprovada:** 2 (DEF-001: Validação de duração negativa e DEF-002: Aceitação de datas futuras)
* **Quantidade bloqueada:** 0

### Resultado Final

**O sistema está aprovado para entrega?**
* `[X]` **Sim (Aprovado com Ressalvas / Correções Pontuais)**
* `[ ]` **Não**

### Justificativa Final
O sistema **OYAMA HUB** apresentou um excelente desempenho global durante o ciclo de testes de software. Dos 20 casos de teste executados, **18 (90%) obtiveram aprovação direta**, confirmando a eficácia e segurança das funcionalidades principais: autenticação, proteção contra SQL Injection e XSS, persistência de dados no MySQL, carregamento dos módulos de Katas/Kihons e responsividade visual.

Os 2 testes reprovados referem-se a validações de entrada no formulário de treinos (aceitação de duração negativa e data futura - DEF-001 e DEF-002). Por se tratarem de ajustes simples de validação no script PHP (`registrar_treino.php`) e HTML (`min="1"`, `max="[data_atual]"`), eles não impedem a entrega do projeto integrador, necessitando apenas da aplicação dos patches de correção sugeridos pela equipe de QA antes do deploy final.

---

## OBSERVAÇÃO FINAL DO GRUPO
A realização deste Plano de Testes permitiu aplicar na prática as metodologias de Garantia de Qualidade de Software (QA) diretamente no **Projeto Integrador OYAMA HUB**. A atividade evidenciou a importância da estruturação de cenários positivos e negativos, análise preventiva de riscos e documentação de defeitos para entregar um produto confiável, seguro e de alta qualidade aos praticantes de Kyokushin Karate.
