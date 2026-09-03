# 🥋 OYAMA HUB — Plano Estratégico de Melhorias Futuras
**Documento Técnico de Arquitetura, Segurança, Otimização e UI/UX**  
*Versão:* 1.0 | *Data:* Março de 2026 | *Status:* Planejado / Em Evolução

---

## 📑 Sumário Executivo

O **Oyama Hub** é uma plataforma dedicada à comunidade do Karate Kyokushin, integrando:
- **Área Pública (Landing Page):** Divulgação da arte marcial, história, técnicas fundamentais e faixas.
- **Área do Aluno/Praticante (Dashboard):** Registro de treinos, monitoramento de horas treinadas, estudo de Katas e Kihons, notas pessoais e perfil.
- **Área de Gestão/Administrativa (`/admin`):** Endpoints REST API e telas para administração de usuários, faixas, exercícios, treinos e estatísticas.

Este planejamento estabelece um roteiro de melhorias técnicas e visuais detalhadas, categorizadas por impacto e com especificações de código "Antes vs. Depois".

---

## 🛡️ Eixo 1: Segurança da Informação & Proteção de Dados (Hardening)

### 1.1. Proteção contra CSRF (Cross-Site Request Forgery)
* **Cenário Atual:** A maioria das requisições `POST` (como atualização de perfil, upload de fotos, registro e exclusão de treinos) confia exclusivamente na sessão ativa sem token de validação de origem.
* **O que muda:** Implementação de um middleware/helper global de tokens CSRF.
* **Impacto Técnico:**
  ```php
  // helpers/csrf.php
  function gerar_csrf_token(): string {
      if (empty($_SESSION['csrf_token'])) {
          $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
      }
      return $_SESSION['csrf_token'];
  }

  function validar_csrf_token(?string $token): bool {
      return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
  }
  ```
  Todos os formulários passam a conter:
  ```html
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(gerar_csrf_token()) ?>">
  ```

### 1.2. Proteção do Diretório de Uploads (`/uploads`)
* **Cenário Atual:** Arquivos enviados em `uploads/perfil/` ficam em diretório com acesso direto pelo navegador. Se um arquivo PHP for injetado, o servidor pode executá-lo.
* **O que muda:** Bloquear execução de scripts dentro do diretório `/uploads/` via `.htaccess` dedicado.
* **Implementação Técnica (`uploads/.htaccess`):**
  ```apache
  # Desabilita execução de qualquer script no diretório de uploads
  <FilesMatch "\.(php|phtml|php3|php4|php5|php7|phps|cgi|pl|exe)$">
      Order Deny,Allow
      Deny from all
  </FilesMatch>
  Options -Indexes -ExecCGI
  php_flag engine off
  ```

### 1.3. Cabeçalhos de Segurança HTTP via `.htaccess`
* **Cenário Atual:** O `.htaccess` atual gerencia apenas rotas de reescrita da API de admin. Faltam headers que previnem clickjacking, sniffing de MIME e ataques XSS.
* **O que muda:** Adicionar cabeçalhos de segurança na raiz do servidor web:
  ```apache
  <IfModule mod_headers.c>
      Header set X-Content-Type-Options "nosniff"
      Header set X-Frame-Options "SAMEORIGIN"
      Header set X-XSS-Protection "1; mode=block"
      Header set Referrer-Policy "strict-origin-when-cross-origin"
      Header set Permissions-Policy "geolocation=(), microphone=(), camera=()"
      Header set Content-Security-Policy "default-src 'self' https: data:; script-src 'self' 'unsafe-inline' https://vlibras.gov.br; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com;"
  </IfModule>
  ```

### 1.4. Bloqueio contra Ataques de Força Bruta no Login
* **Cenário Atual:** Não há limitação de tentativas de login por IP ou e-mail.
* **O que muda:** Criação de tabela de controle `login_tentativas` para registrar falhas consecutivas e aplicar bloqueio temporário (ex: 5 falhas = 15 minutos de bloqueio).
* **Estrutura SQL:**
  ```sql
  CREATE TABLE IF NOT EXISTS login_tentativas (
      id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      ip VARCHAR(45) NOT NULL,
      email VARCHAR(150) NOT NULL,
      tentativa_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_ip_email (ip, email, tentativa_em)
  );
  ```

### 1.5. Proteção de Credenciais e Arquivos Sensíveis
* **Cenário Atual:** O arquivo `php/config.php` armazena dados de conexão diretamente no código. Arquivos como `.git` e `.vscode` podem ficar acessíveis caso o servidor web não os bloqueie.
* **O que muda:**
  - Bloquear `.git`, `.env`, `.docx`, `.sql` e `.md` de acesso direto via HTTP.
  - Implementação de arquivo `.env` para credenciais do banco.

---

## ⚡ Eixo 2: Performance, Otimização & SEO

### 2.1. Políticas de Cache Estático e Compressão Gzip/Brotli
* **Cenário Atual:** Recursos estáticos (CSS, JS, imagens PNG/JPG) são requisitados em toda nova visita sem instruções explícitas de cache longo.
* **O que muda:** Adicionar regras de expiração e compressão no `.htaccess`:
  ```apache
  <IfModule mod_deflate.c>
      AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css application/javascript application/json image/svg+xml
  </IfModule>

  <IfModule mod_expires.c>
      ExpiresActive On
      ExpiresByType image/jpg "access plus 1 month"
      ExpiresByType image/jpeg "access plus 1 month"
      ExpiresByType image/png "access plus 1 month"
      ExpiresByType image/webp "access plus 1 month"
      ExpiresByType text/css "access plus 1 week"
      ExpiresByType application/javascript "access plus 1 week"
  </IfModule>
  ```

### 2.2. Conversão Automática de Imagens para WebP no Upload
* **Cenário Atual:** O avatar do usuário em `dashboard/foto_perfil.php` aceita JPG/PNG e salva o arquivo cru de até 5MB.
* **O que muda:** Redimensionamento e conversão em tempo real para WebP com tamanho padrão (ex: 300x300px, 85% qualidade), reduzindo arquivos de 3MB para ~35KB.
* **Vantagem:** Redução drástica de uso de disco e carregamento instantâneo do topo do dashboard.

### 2.3. Paginação e Lazy Querying nas Consultas do Dashboard
* **Cenário Atual:** Listagens de treinos em `dashboard/treinos.php` e usuários em `php/admin/users.php` trazem todos os registros de uma só vez.
* **O que muda:** Implementação de paginação paginada (`LIMIT 15 OFFSET X`) com seletor de páginas e busca assíncrona por AJAX/Fetch.

### 2.4. SEO Técnico e Metadados Open Graph
* **Cenário Atual:** `index.html` possui metatags básicas, mas carece de protocolos para compartilhamento social em WhatsApp, Telegram, Facebook e Twitter.
* **O que muda:**
  ```html
  <meta property="og:title" content="Oyama Hub | Karate Kyokushin">
  <meta property="og:description" content="Plataforma de treino, fundamentos e graduação no Karate de combate real.">
  <meta property="og:image" content="https://seusite.com/img/og-preview.jpg">
  <meta property="og:type" content="website">
  <meta name="twitter:card" content="summary_large_image">
  ```

---

## 🎨 Eixo 3: Experiência Visual, UI/UX e Design System

### 3.1. Design System: Unificação de Cores e Contraste no Light Mode
* **Cenário Atual:** O modo claro possui classes pontuais que aplicam vermelho em textos corridos (`--text: #c8000a`), o que pode cansar a visão em leituras longas.
* **O que muda:**
  - Ajustar o contraste do Light Mode para tons editoriais equilibrados:
    - Fundo: `#f8f6f2` (papel japonês/creme).
    - Superfície: `#ffffff`.
    - Texto base: `#1f1c1a` (antracite legível com alto contraste).
    - Destaques/Acentos: `#c8000a` (vermelho carmesim de assinatura para botões, badges e ícones).
  - Transição de tema com efeito suave via CSS:
    ```css
    * {
        transition: background-color 0.25s cubic-bezier(0.4, 0, 0.2, 1),
                    border-color 0.25s cubic-bezier(0.4, 0, 0.2, 1),
                    color 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    ```

### 3.2. Centralização dos Ícones SVG em um Sprite System
* **Cenário Atual:** No `dashboard.php`, cada link de acesso rápido repete código SVG bruto inline de 10 a 20 linhas.
* **O que muda:**
  - Criar um componente centralizado `includes/icons.php` ou utilizar a tag `<use>` com um arquivo `icons.svg`.
  - Código limpo no HTML:
    ```html
    <!-- Antes: 20 linhas de SVG inline -->
    <!-- Depois: -->
    <?= render_icon('treinos', 20) ?>
    ```

### 3.3. Sistema de Notificações Toast Não-Bloqueantes
* **Cenário Atual:** Mensagens de feedback utilizam parâmetros na URL (`perfil.php?foto=salva`) ou modais que bloqueiam toda a tela.
* **O que muda:** Sistema de Toasts dinâmicos no canto inferior direito com auto-dismiss (desaparecem em 3.5 segundos), com suporte a Sucesso, Aviso e Erro.
* **Exemplo Visual:**
  - Toast escuro com borda vermelha iluminada e animação de entrada com deslizamento (*Slide-in*).

### 3.4. Melhoria de Interação nas Tabelas Administrativas
* **Cenário Atual:** As telas administrativas usam tabelas convencionais que podem gerar scroll horizontal no mobile.
* **O que muda:**
  - Layout *Data Cards* responsivo no mobile: em telas < 768px, as linhas da tabela se convertem automaticamente em cartões modulares com informações empilhadas.

---

## 🏛️ Eixo 4: Arquitetura de Código & Qualidade

### 4.1. Transição de `mysqli` para `PDO` (PHP Data Objects)
* **Cenário Atual:** Uso de funções `mysqli_*` espalhadas pelos arquivos.
* **O que muda:** Introduzir uma classe singleton `Database::getConnection()` usando PDO.
* **Vantagens Técnicas:**
  - Tratamento nativo com `try/catch` de `PDOException`.
  - Binding com tipos automáticos e objetos retornados diretamente (`PDO::FETCH_ASSOC`, `PDO::FETCH_OBJ`).
  - Código mais conciso e padronizado:
    ```php
    // Exemplo de transição para PDO:
    $stmt = $pdo->prepare("SELECT * FROM treinos WHERE usuario_id = :uid ORDER BY data_treino DESC LIMIT :limite");
    $stmt->execute([':uid' => $userId, ':limite' => 10]);
    $treinos = $stmt->fetchAll();
    ```

### 4.2. Trilha de Auditoria Automática na Tabela `atividades`
* **Cenário Atual:** A tabela `atividades` existe no banco `sql.sql`, mas tem pouquíssima inserção no fluxo real do sistema.
* **O que muda:** Criar uma função utilitária `registrar_atividade($usuario_id, $acao, $detalhes)` chamada automaticamente em:
  1. Login e Logout.
  2. Alteração de senha.
  3. Atualização de foto de perfil.
  4. Mudança de faixa do aluno (acionada pelo administrador).
  5. Criação ou exclusão de treinos.

### 4.3. Padronização das Respostas da API (`/admin/api/*`)
* **Cenário Atual:** Algumas APIs retornam respostas sem estrutura uniforme.
* **O que muda:** Respostas com envelope padronizado em JSON:
  ```json
  {
    "status": "success",
    "code": 200,
    "data": { ... },
    "message": "Registro atualizado com sucesso"
  }
  ```

---

## 📅 Matriz de Priorização (Roadmap de Execução)

| Fase | Ação | Complexidade | Impacto | Categoria |
| :--- | :--- | :---: | :---: | :---: |
| **Fase 1** | Proteção de uploads via `.htaccess` | Baixa | 🔴 Crítico | Segurança |
| **Fase 1** | Headers HTTP de Segurança (`.htaccess`) | Baixa | 🔴 Crítico | Segurança |
| **Fase 1** | Implementação de Tokens CSRF nos formulários | Média | 🔴 Crítico | Segurança |
| **Fase 2** | Redimensionamento e conversão WebP de fotos | Média | 🟠 Alto | Performance |
| **Fase 2** | Compressão Gzip e Cache de estáticos via Apache | Baixa | 🟠 Alto | Performance |
| **Fase 2** | Auditoria e Logs automáticos em `atividades` | Baixa | 🟠 Alto | Arquitetura |
| **Fase 3** | Ajuste de contraste e transições do Light Mode | Média | 🟡 Médio | UI/UX |
| **Fase 3** | Centralização de Ícones SVG (`icons.php`) | Baixa | 🟡 Médio | Limpeza |
| **Fase 3** | Sistema de Toasts/Notificações assíncronas | Média | 🟡 Médio | UI/UX |
| **Fase 4** | Paginação com busca AJAX em Treinos e Usuários | Média | 🟡 Médio | Performance |
| **Fase 4** | Migração do banco para classe Singleton PDO | Alta | 🟠 Alto | Arquitetura |

---

## 🚀 Como Utilizar Este Planejamento

1. As melhorias da **Fase 1 (Segurança)** devem ser priorizadas antes de colocar a aplicação em ambiente de produção (hospedagem pública).
2. As melhorias da **Fase 2 (Otimização)** garantem carregamento ultrarrápido mesmo em redes móveis 3G/4G.
3. As melhorias das **Fases 3 e 4** consolidam o Oyama Hub como uma aplicação moderna, limpa e com código de nível profissional.
