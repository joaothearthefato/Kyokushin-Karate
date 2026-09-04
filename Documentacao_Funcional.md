# Documentação Funcional e Relatório Mestre (Master Patch Notes)
**Sistema:** Oyama Hub – Plataforma de Treinamento de Kyokushin Karate
**Data da Documentação:** Setembro de 2026
**Papel:** Arquiteto de Software e Tech Lead

---

## 1. Visão Geral e Arquitetura
O **Oyama Hub** é uma aplicação web voltada para a organização, registro de treinos e acompanhamento da evolução técnica de praticantes de Kyokushin Karate. O sistema permite o cadastro de usuários, gestão de perfis com faixas de graduação, e funciona como uma biblioteca técnica multimídia integrada ao YouTube.

**Tech Stack da Aplicação:**
*   **Backend:** PHP 8+ (Estrutura modular com includes)
*   **Frontend:** HTML5, CSS3 (Vanilla), JavaScript
*   **Banco de Dados:** MySQL (via PDO e Prepared Statements)
*   **Infraestrutura:** XAMPP (Apache web server)
*   **Design System:** CSS Custom Properties (Variáveis de Tema Dark/Light)

**Sitemap (Mapa do Site):**
*   `/` (Landing Page)
*   `/php/login.php` (Autenticação)
*   `/php/registro.php` (Cadastro)
*   `/dashboard/treinos.php` (Meus Treinos)
*   `/dashboard/katas.php` (Biblioteca de Katas)
*   `/dashboard/kihons.php` (Biblioteca de Kihons)
*   `/dashboard/progresso.php` (Evolução Técnica)
*   `/dashboard/perfil.php` (Gestão do Aluno)

---

## 2. Matriz de Acessos e Permissões
| Módulo / Rota | Visitante (Não Autenticado) | Aluno (Autenticado) | Administrador (Se aplicável) |
| :--- | :---: | :---: | :---: |
| Landing Page (`index.html`) | ✅ | ✅ | ✅ |
| Cadastro (`registro.php`) | ✅ | ❌ (Redireciona) | ✅ |
| Login (`login.php`) | ✅ | ❌ (Redireciona) | ✅ |
| Dashboard Principal | ❌ | ✅ | ✅ |
| Módulo de Treinos | ❌ | ✅ | ✅ |
| Biblioteca (Katas/Kihons) | ❌ | ✅ | ✅ |
| Gerenciar Conta/Perfil | ❌ | ✅ (Apenas sua conta) | ✅ |
| Painel Admin (`/php/admin/`) | ❌ | ❌ | ✅ |

---

## 3. Mapeamento Detalhado por Módulo (Tela a Tela)

### 3.1. Landing Page (Home)
*   **Rota/URL:** `/index.html`
*   **Elementos da Tela:** Hero section de apresentação, cards de funcionalidades, botões de Call-to-Action (CTA) para Cadastro e Login.
*   **Ações e Regras:** Apresenta a plataforma. CTAs direcionam estritamente para os fluxos de autenticação.

### 3.2. Módulo de Autenticação e Cadastro
*   **Rota/URL:** `/php/login.php` e `/php/registro.php`
*   **Elementos da Tela:** Formulário de e-mail e senha, formulário de dados pessoais, botões de submissão, alertas de erro.
*   **Ações e Regras:** 
    *   **Login:** Verifica credenciais com `password_verify()` no banco de dados MySQL. Se sucesso, inicia `$_SESSION` e direciona ao Dashboard.
    *   **Cadastro:** Verifica duplicidade de e-mail. Exige senha forte e salva hash no banco.

### 3.3. Dashboard Principal (Visão Geral)
*   **Rota/URL:** `/php/dashboard.php`
*   **Elementos da Tela:** Cards de KPIs (Total de treinos, Horas treinadas), mini-gráfico de frequência (últimos 6 meses), resumo de técnicas dominadas, navbar superior e menu de atalhos.
*   **Ações e Regras:** Agrega os dados usando a `$_SESSION['id']`. Calcula porcentagens baseando-se em totais usando a função `max()` nativa para evitar divisão por zero matematicamente.

### 3.4. Módulo de Treinos
*   **Rota/URL:** `/dashboard/treinos.php`
*   **Elementos da Tela:** Histórico de treinos (paginado), filtros por mês e ano, formulário de "Registrar Treino" à direita (Data, Descrição, Duração em Minutos e Exercícios dinâmicos).
*   **Ações e Regras:**
    *   O usuário pode listar, editar (`editar_treino.php`) e excluir (`deletar_treino.php`) os próprios treinos.
    *   A submissão impede o registro de datas futuras (`max="date()"`) e exige duração mínima de 5 minutos (validação Server-side em `registrar_treino.php`).

### 3.5. Bibliotecas Técnicas (Katas e Kihons)
*   **Rota/URL:** `/dashboard/katas.php` e `/dashboard/kihons.php`
*   **Elementos da Tela:** Campo de busca, botões de filtro por faixa (Iniciante, Intermediário, Avançado), grid de cards com numeração e botão "Play", botão de "Concluir/Marcar como feito". Modal de vídeo flutuante.
*   **Ações e Regras:** 
    *   Filtra via Javascript os cards exibidos.
    *   Ao clicar no vídeo, aciona um iframe modal do YouTube dinâmico. O layout do iframe é 100% responsivo usando CSS Grid e `aspect-ratio: 16/9`.
    *   Permite marcar a técnica como dominada (salva via requisição para `toggle_progresso.php`).

### 3.6. Perfil e Configurações
*   **Rota/URL:** `/dashboard/perfil.php`
*   **Elementos da Tela:** Informações pessoais, foto do perfil, alteração de faixa atual, atualização de senha, botão de logout e controle de Dark/Light mode.
*   **Ações e Regras:** Atualizações salvam em tempo real. Logout destrói a sessão e encerra a conexão.

---

## 4. Serviços e Integrações de Terceiros
1. **YouTube Embedded API:**
   *   **Uso:** Fornece os vídeos de Katas e Kihons sem sobrecarregar a hospedagem local.
   *   **Integração:** `<iframe>` utilizando os parâmetros `?autoplay=1&rel=0` injetados dinamicamente via JavaScript quando o modal é aberto.
2. **VLibras (Gov.br):**
   *   **Uso:** Acessibilidade em libras.
   *   **Integração:** Script global injetado via widget no frontend para tradução da interface.

---

## 5. Segurança e Validações
A arquitetura do sistema adota múltiplos pilares de segurança e prevenção a vulnerabilidades (OWASP):
*   **Prevenção a SQL Injection:** Todo o CRUD com o banco de dados ocorre estritamente por meio de **Prepared Statements (PDO / MySQLi prepare)** na classe `Database.php`. Variáveis nunca são concatenadas diretamente nas queries.
*   **Prevenção a CSRF (Cross-Site Request Forgery):** Existe uma rotina (`php/csrf.php`) que gera um token seguro e exige a submissão desse token (via `<input type="hidden">`) em todos os formulários (`POST`).
*   **Fluxo de Autenticação e Hijacking:** Validação contínua através do arquivo `auth_check.php` incluso no topo de páginas privadas. Se `$_SESSION['id']` for nulo, força o redirecionamento imediato.
*   **Tratamento de Erros e Logs:** Desligamento do report brutal do PHP (`mysqli_report(MYSQLI_REPORT_OFF)`). Em caso de queda do banco, exibe *"Serviço temporariamente indisponível"* em vez dos caminhos internos, protegendo a topologia do servidor (Correção aplicada pós-Auditoria).

---

## 6. Estado Atual e Capacidades (Master Patch Notes)
**Status Geral do Sistema:** 🟢 **100% Funcional (Versão Estável - Setembro/2026)**

### Patch Notes e Auditoria Recente
*   ✅ **Formulários Refatorados:** Impede cadastro de durações negativas (`< 5 min`) e datas futuras. Validações duplo-cheque (Frontend HTML5 + Backend PHP).
*   ✅ **Dashboard Seguro:** Corrigidos riscos de *Warning: Division by zero* no processamento matemático da porcentagem de kihons/katas em alunos recém-cadastrados.
*   ✅ **Responsividade Plena:** Removidos parâmetros estáticos do player de vídeo e implementado design fluido (`aspect-ratio`), rodando sem quebras de layout em celulares (`< 360px`).
*   ✅ **Segurança do DB Aprimorada:** Ocultados caminhos do sistema em caso de shutdown no MySQL.
*   ⏳ **Planejado/Futuro:** Sincronização PWA offline para treinos em dojos sem internet (em backlog) e integração com gateway para mensalidades (em planejamento).
