# Auditoria Web Completa — Oyama Hub / Kyokushin Karate

**Data da auditoria:** 11 de setembro de 2026  
**Escopo:** análise estática do workspace `c:\xampp\htdocs\Kyokushin-Karate`  
**Tipo de instalação observada:** aplicação local em XAMPP/Apache, com PHP e MySQL/MariaDB  
**Status dos dados de audiência:** não disponíveis no repositório

> Este relatório foi produzido a partir da inspeção dos arquivos do projeto: HTML, PHP, CSS, JavaScript, SQL, `.htaccess`, includes, documentação e assets. Os achados de código são verificáveis no workspace. Métricas de usuários, tráfego, Web Vitals reais, conversões, disponibilidade e histórico de produção exigem acesso ao servidor, navegador em execução, logs, Google Analytics, Search Console ou outra ferramenta de observabilidade.

---

## Sumário Executivo

O Oyama Hub é uma aplicação de gerenciamento de treinos de Kyokushin Karate com landing page pública, cadastro/login, dashboard do praticante, catálogo de Katas e Kihons, registro de treinos, progresso, anotações, perfil e painel administrativo.

A base possui uma identidade visual consistente, um conjunto amplo de funcionalidades e várias boas práticas já aplicadas, como prepared statements em muitos fluxos, hash de senhas, CSRF em parte dos formulários, regeneração da sessão no login, controle de propriedade de treinos/anotações, cache e compressão configurados no Apache e uso de WebP.

Os maiores riscos encontrados são operacionais e de segurança:

1. Credencial de banco de dados presente no código-fonte em `php/config.php` e `php/Database.php`.
2. APIs administrativas mutáveis sem validação CSRF centralizada.
3. Exclusão de treino permitida via GET, sem CSRF obrigatório.
4. Alteração de progresso sem CSRF.
5. Login sem verificar o campo `usuarios.ativo`.
6. Recuperação de senha apenas simulada visualmente, sem envio real, token ou redefinição.
7. Upload de avatar com fallback para salvar arquivo original e sem regra específica de execução em `uploads/perfil`.
8. Duplicação de navbar, modal/toast e camada de banco.
9. Ausência de Analytics, Search Console, sitemap, robots, canonical, Open Graph e dados estruturados.
10. Links legais do rodapé apontando para `#`.
11. Modelo de dados mais rico que o fluxo de registro de treinos: campos como `nome`, `descricao`, `nivel` e `exercicio_id` não são plenamente utilizados pelo fluxo principal.
12. Ausência de testes automatizados e de métricas históricas confiáveis.

A recomendação é corrigir primeiro segredos e autorização/CSRF, depois consolidar arquitetura e modelo de dados, e somente então medir e otimizar conversão, SEO e performance com dados reais.

---

# 1. Visão Geral e Arquitetura do Site

## 1.1 Identificação

### Nome e proposta observada

- **Nome de produto:** Oyama Hub.
- **Domínio temático:** Kyokushin Karate.
- **Propósito:** organizar treinos, técnicas, graduação, progresso e anotações de praticantes.
- **Público primário:** alunos/praticantes de Kyokushin.
- **Público secundário:** professores, administradores de dojo e responsáveis por acompanhar evolução.

### URL principal

A URL pública pretendida, considerando o workspace XAMPP e o `.htaccess`, é provavelmente:

```text
http://localhost/Kyokushin-Karate/index.html
```

Não foi fornecido domínio público, hostname de produção ou URL externa. Portanto não é possível confirmar:

- domínio canônico;
- subdomínios;
- ambiente de homologação;
- CDN;
- provedor de hospedagem;
- localização geográfica do servidor;
- DNS;
- certificado de produção.

### Tecnologia

- Backend: PHP procedural, sem framework identificável.
- Banco: MySQL ou MariaDB.
- Acesso ao banco: `mysqli` e PDO simultaneamente.
- Servidor presumido: Apache via XAMPP.
- Roteamento: arquivos PHP diretos, com tentativa de `mod_rewrite` no `.htaccess`.
- Frontend: HTML, CSS modular e JavaScript vanilla.
- Persistência de preferências no navegador: `localStorage`.
- Gráficos: Canvas/JavaScript na área de progresso.
- Integrações externas observadas: Google Fonts e YouTube.
- Upload: fotos de perfil em `uploads/perfil`.
- APIs: endpoints JSON em `php/admin/api`.

## 1.2 Estrutura de diretórios

### Documentação

- `Documentacao_Funcional.md`: documentação funcional.
- `PLANEJAMENTO.md`: planejamento do projeto.
- `Projeto_Apresentacao.md`: material de apresentação.
- `Requisitos.md`: requisitos.
- `RequisitosSites.docx`: documento adicional de requisitos.

### Entrada pública e autenticação

- `index.html`: landing page.
- `php/login.php`: login.
- `php/registro.php`: formulário de cadastro.
- `php/registrar_aluno.php`: processamento do cadastro.
- `php/logout.php`: logout.
- `php/dashboard.php`: dashboard principal do aluno.
- `php/acessibilidade.php`: documentação/página de acessibilidade.

### Núcleo da aplicação

- `php/config.php`: conexão/configuração do banco.
- `php/Database.php`: singleton PDO.
- `php/auth_check.php`: helpers de autenticação/autorização.
- `php/csrf.php`: geração e validação de tokens CSRF.
- `php/setup_db.php`: instalação/migração via PHP.
- `php/navbar.php`: navbar legada/alternativa.

### Área do praticante

- `dashboard/treinos.php`: listagem, filtros, sugestões, registro e edição via modal.
- `dashboard/registrar_treino.php`: criação de treino.
- `dashboard/editar_treino.php`: formulário legado de edição.
- `dashboard/atualizar_treino.php`: processamento da edição.
- `dashboard/deletar_treino.php`: exclusão de treino.
- `dashboard/katas.php`: catálogo e progresso de Katas.
- `dashboard/kihons.php`: catálogo e progresso de Kihons.
- `dashboard/toggle_progresso.php`: alternância de conclusão.
- `dashboard/progresso.php`: resumo, histórico, gráficos e conquistas.
- `dashboard/anotacoes.php`: CRUD de anotações.
- `dashboard/perfil.php`: dados, foto, faixa e edição do perfil.
- `dashboard/foto_perfil.php`: upload/remoção da foto.

### Área administrativa

- `php/admin/header.php`: shell administrativo.
- `php/admin/footer.php`: JavaScript/API do admin.
- `php/admin/index.php`: resumo administrativo.
- `php/admin/users.php`: usuários.
- `php/admin/katas.php`: Katas.
- `php/admin/kihons.php`: Kihons.
- `php/admin/treinos.php`: treinos.
- `php/admin/exercicios.php`: exercícios.
- `php/admin/faixas.php`: faixas.
- `php/admin/progresso.php`: ranking/progresso.
- `php/admin/configuracoes.php`: configurações.
- `php/admin/api/bootstrap.php`: bootstrap de APIs e autorização.
- `php/admin/api/users.php`: API de usuários.
- `php/admin/api/katas.php`: API de Katas.
- `php/admin/api/kihons.php`: API de Kihons.
- `php/admin/api/treinos.php`: API de treinos.
- `php/admin/api/exercicios.php`: API de exercícios.
- `php/admin/api/faixas.php`: API de faixas.
- `php/admin/api/progresso.php`: API de progresso.
- `php/admin/api/stats.php`: API de estatísticas.

### Includes

- `includes/navbar.php`: navbar principal usada na área do aluno.
- `includes/toast.php`: sistema de toast legado.
- `includes/icons.php`: ícones/helpers.

### Frontend

- `js/acessibilidade.js`: painel de acessibilidade, TTS e preferências.
- `js/app-modal.js`: modal/toast universal.
- `js/landing.js`: tabs, explorer de faixas, filtros e interações da landing.
- `js/theme.js`: tema.

CSS por contexto:

- `css/tokens.css`: design tokens, reset, temas e base.
- `css/style.css`: landing.
- `css/navbar.css`: navbar.
- `css/dashboard.css`: aliases/base do dashboard.
- `css/dash_home.css`: dashboard.
- `css/treinos.css`: treinos.
- `css/katas.css`: Katas.
- `css/kihon.css`: Kihons.
- `css/progresso.css`: progresso.
- `css/anotacoes.css`: anotações.
- `css/perfil.css`: perfil.
- `css/perfil_aluno.css`: estilo alternativo/legado de perfil.
- `css/registerlogin.css`: login/registro.
- `css/registro.css`: estilo adicional/legado de registro.
- `css/admin.css`: painel administrativo.
- `css/acessibilidade.css`: painel de acessibilidade.
- `css/app-modal.css`: modal/toast universal.

### Banco e servidor

- `database/schema.sql`: schema consolidado e seed.
- `sql/sql.sql`: segunda definição/arquivo SQL identificado no inventário.
- `.htaccess`: rewrite, bloqueio de arquivos, CSP, compressão e cache.
- `.vscode/launch.json`: configuração de execução/debug.

### Assets

- `img/kyokushinicon.png`: ícone/logo.
- `img/photo1.webp`: imagem hero.
- `img/oyama.webp`: imagem de Masutatsu Oyama.
- `img/kimono.webp`: equipamento.
- `img/faixa.webp`: faixa.
- `img/protetores.webp`: protetores.
- `uploads/perfil/*`: fotos de usuários.

## 1.3 Mapa completo de páginas e fluxos

### Público

| Rota/arquivo | Função | Proteção |
|---|---|---|
| `index.html` | Landing e apresentação | Pública |
| `php/login.php` | Login | Pública |
| `php/registro.php` | Cadastro | Pública |
| `php/acessibilidade.php` | Explicação de acessibilidade | Pública ou sessão conforme fluxo |

### Aluno autenticado

| Rota/arquivo | Função |
|---|---|
| `php/dashboard.php` | Resumo da conta e atalhos |
| `dashboard/treinos.php` | Histórico, filtros, sugestões e registro |
| `dashboard/katas.php` | Catálogo/progresso de Katas |
| `dashboard/kihons.php` | Catálogo/progresso de Kihons |
| `dashboard/progresso.php` | Estatísticas, histórico e conquistas |
| `dashboard/anotacoes.php` | Diário pessoal |
| `dashboard/perfil.php` | Perfil, foto, faixa e senha |
| `php/logout.php` | Encerramento de sessão |

### Processadores autenticados

| Rota/arquivo | Método esperado | Função |
|---|---|---|
| `php/registrar_aluno.php` | POST | Cria usuário |
| `dashboard/registrar_treino.php` | POST | Cria treino e exercícios |
| `dashboard/atualizar_treino.php` | POST | Atualiza treino |
| `dashboard/deletar_treino.php` | deveria ser somente POST | Exclui treino |
| `dashboard/toggle_progresso.php` | POST/AJAX | Alterna progresso |
| `dashboard/foto_perfil.php` | POST multipart | Upload/remoção de foto |

### Administração

O painel administrativo contém páginas de gestão e APIs JSON. O acesso de páginas usa `require_admin()` no header, mas a proteção CSRF das mutações precisa ser aplicada também nas APIs.

## 1.4 Proposta de valor

A proposta central é transformar o site em um centro digital para o praticante de Kyokushin:

- aprender Katas e Kihons;
- registrar sessões de treino;
- acompanhar minutos, frequência e evolução;
- visualizar jornada de faixas;
- guardar anotações pessoais;
- usar sugestões de treino por graduação;
- permitir ao administrador manter conteúdo e usuários.

A landing reforça uma identidade marcial e aspiracional. O produto deixa de ser apenas uma página institucional e funciona como uma aplicação de acompanhamento de prática.

---

# 2. Infraestrutura, Desempenho e Segurança

## 2.1 Hospedagem e servidor

### O que foi comprovado

- O projeto está em `c:\xampp\htdocs`, indicando uso local de XAMPP.
- `.htaccess` usa `mod_rewrite`, `mod_headers`, `mod_deflate` e `mod_expires`.
- O `RewriteBase` está definido como `/Kyokushin-Karate/`.
- A aplicação usa `localhost` como host de banco em `php/config.php`.

### O que não foi comprovado

Não há dados suficientes para afirmar:

- provedor de produção;
- datacenter/localização;
- versão do Apache;
- versão do PHP em produção;
- versão MySQL/MariaDB;
- HTTP/1.1, HTTP/2 ou HTTP/3;
- TLS/SSL válido em produção;
- CDN;
- balanceador;
- autoscaling;
- backup automatizado;
- monitoramento;
- SLA.

## 2.2 Performance e Web Vitals

Não foram encontrados dados reais de Lighthouse, CrUX, RUM ou PageSpeed. Portanto, os valores abaixo não podem ser preenchidos com números confiáveis:

| Métrica | Desktop | Mobile | Status |
|---|---:|---:|---|
| LCP | Não medido | Não medido | Requer navegador/produção |
| INP | Não medido | Não medido | Requer interação real |
| CLS | Não medido | Não medido | Requer execução |
| FCP | Não medido | Não medido | Requer execução |
| TTFB | Não medido | Não medido | Requer servidor |
| Tempo total | Não medido | Não medido | Requer rede/ambiente |

### Riscos potenciais de performance

1. Várias famílias de fontes e pesos são requisitadas em diferentes páginas.
2. Há `@import` e `<link>` para Google Fonts, aumentando dependência externa.
3. Thumbnails e vídeos do YouTube adicionam requisições de terceiros.
4. A aplicação usa consultas múltiplas no dashboard e progresso.
5. O dashboard faz consultas separadas para vários indicadores.
6. A página de anotações executa `CREATE TABLE IF NOT EXISTS` durante a requisição.
7. Algumas APIs administrativas não impõem paginação de maneira uniforme.
8. O painel administrativo pode montar grandes listas no navegador.
9. O cache é configurado por extensão, mas não foi verificado em uma resposta HTTP real.
10. A compressão GZIP é declarada no `.htaccess`, mas não foi medida no wire.
11. Fontes externas podem causar alteração de layout antes do carregamento.

### Pontos positivos

- Imagens principais em WebP.
- `loading="lazy"` em parte das imagens.
- Cache e compressão declarados no `.htaccess`.
- Batch query de exercícios dos treinos da página atual.
- Índice `idx_usuario_data` em `treinos`.
- Índices por categoria, nível e faixa no schema.
- Assets estáticos separados por contexto.

## 2.3 Segurança HTTP

### Cabeçalhos configurados no `.htaccess`

- `X-Content-Type-Options: nosniff`.
- `X-Frame-Options: SAMEORIGIN`.
- `Referrer-Policy: strict-origin-when-cross-origin`.
- `Permissions-Policy` restritiva para geolocalização, microfone, câmera, pagamento e USB.
- Remoção de `X-Powered-By` e tentativa de remover `Server`.
- CSP com `default-src 'self'`, fontes Google, YouTube e regras de formulário.

### Problemas da CSP

1. `'unsafe-inline'` é permitido em `script-src`, reduzindo a proteção contra XSS.
2. Muitos scripts inline e `onclick` justificam a exceção, mas o ideal é migrar para listeners externos.
3. A CSP ainda menciona `vlibras.gov.br`, embora a integração tenha sido removida. Isso é configuração obsoleta e amplia desnecessariamente a política.
4. `img-src 'self' data: https:` permite qualquer host HTTPS para imagens, mais amplo que o necessário.
5. `frame-src` permite YouTube, mas o uso deve ser documentado e restrito ao necessário.
6. Não há `upgrade-insecure-requests`.
7. Não há `Strict-Transport-Security`; só deve ser adicionado quando toda a implantação estiver em HTTPS.

### Segredos

`php/config.php` contém usuário e senha de banco diretamente no código. `php/Database.php` recebe os mesmos valores. Esse é o achado de maior criticidade.

**Ação obrigatória:**

- revogar/rotacionar a senha exposta;
- remover o segredo do código e do histórico Git;
- usar variáveis de ambiente ou arquivo fora da raiz pública;
- revisar backups e cópias do repositório;
- limitar permissões do usuário do banco;
- não usar `root` pela aplicação.

## 2.4 Autenticação e autorização

### Pontos positivos

- Senhas usam `password_hash`.
- Login usa `password_verify`.
- `session_regenerate_id(true)` é chamado após login.
- Há helper de autenticação e autorização administrativa.
- Treinos verificam `usuario_id` na consulta.
- Anotações verificam `usuario_id` no CRUD.

### Falhas críticas/altas

#### Usuário inativo ainda pode logar

`php/login.php` busca por e-mail, mas não filtra `ativo = 1`. Como o admin pode desativar usuários, a regra de negócio fica incompleta.

Correção esperada:

```sql
SELECT * FROM usuarios WHERE email = ? AND ativo = 1
```

#### Recuperação de senha não implementada

O modal de “esqueci minha senha” apenas simula envio com timeout. Não há:

- token aleatório;
- validade;
- tabela de tokens;
- envio de e-mail;
- endpoint de confirmação;
- redefinição real;
- proteção contra enumeração.

#### Rate limiting ausente

Não foi identificada limitação de tentativas para login, cadastro, APIs administrativas ou recuperação de senha.

#### Sessão não centralizada

Há vários `session_start()` distribuídos. Não foi encontrada configuração explícita e centralizada de:

- `HttpOnly`;
- `Secure`;
- `SameSite`;
- timeout por inatividade;
- renovação periódica;
- modo estrito de sessão.

#### Admin seed previsível

O schema cria `admin@admin.com`. Mesmo com hash, um identificador administrativo previsível facilita tentativas direcionadas.

## 2.5 CSRF

### Proteções existentes

- `php/csrf.php` centraliza token.
- Cadastro, login e vários formulários usam `csrf_input()` e `validar_csrf()`.
- Foto de perfil e treinos têm validações CSRF em seus fluxos principais.

### Falhas

| Endpoint | Problema | Gravidade |
|---|---|---|
| `php/admin/api/*.php` | Mutações administrativas não usam CSRF de forma centralizada | Alta |
| `dashboard/deletar_treino.php` | GET executa exclusão sem token | Alta |
| `dashboard/toggle_progresso.php` | POST altera progresso sem token | Alta |
| `php/logout.php` | Fluxos GET/POST precisam ser padronizados | Média |

## 2.6 Upload de arquivos

O upload de perfil tem boas medidas:

- POST obrigatório;
- CSRF;
- limite de 5 MB;
- validação MIME real em parte do fluxo;
- nomes aleatórios;
- redimensionamento para 400x400 quando GD está disponível.

Riscos:

1. Fallback pode salvar arquivo original.
2. Não foi localizada regra específica em `uploads/perfil` bloqueando execução de scripts.
3. O risco depende da configuração do Apache/MIME.
4. Não há quota por usuário.
5. Não há validação clara de dimensões mínimas/máximas.
6. Uso de `@` pode ocultar erros operacionais importantes.
7. Arquivos ficam em área potencialmente pública.

Recomendação: salvar fora da raiz pública ou aplicar bloqueio explícito de execução e servir imagens por endpoint controlado.

## 2.7 Disponibilidade e erros 404/500

Não foi possível verificar disponibilidade, status HTTP, logs ou ocorrência histórica de 404/500. O workspace não contém logs de produção.

Riscos de erro identificados por inspeção:

- rotas clean do `.htaccess` podem não corresponder ao caminho real `/php/admin`;
- páginas usam shells diferentes e podem ter links relativos inconsistentes;
- `dashboard/kihons.php` usa redirecionamento diferente das demais páginas;
- dependências externas podem falhar sem fallback robusto;
- `php/setup_db.php` pode ser acionado indevidamente pela web.

---

# 3. Análise Exaustiva de Conteúdo e Copywriting

## 3.1 Inventário da landing page

### Hero

- Marca Oyama Hub.
- Mensagem principal sobre dominar a força do Kyokushin.
- CTA de registro.
- CTA para acesso/login.
- Imagem de karateka.
- Chips temáticos de graduação e 100-Man Kumite.
- Kanji decorativo.
- Estatísticas de destaque, como Katas catalogados e graduações guiadas.

### Sobre e história

- História de Masutatsu Oyama.
- Fundação e filosofia do Kyokushin.
- Card do fundador.
- Três pilares:
  - Kihon;
  - Kata;
  - Kumite.

### Técnicas

- Filtros por categorias.
- Técnicas de chutes, socos, joelhos/defesas e esquivas.
- Cards com kanji, zona, título, descrição e dica do sensei.
- Conteúdo educativo e técnico.

### Explorador de faixas

- Branca.
- Laranja.
- Azul.
- Amarela.
- Verde.
- Marrom.
- Marrom com ponta preta.
- Preta.

Cada faixa possui dados alterados pelo JavaScript:

- título;
- Kyu/Dan;
- elemento simbólico;
- filosofia;
- Katas;
- tempo de prática;
- foco técnico;
- condicionamento;
- visual e listras.

### Equipamentos

- Karategi.
- Obi/faixa.
- Protetores oficiais.
- Imagens WebP.
- Tags de categoria.
- Descrição e lista de características.

### Dojo Kun

- Sete preceitos.
- Lista interativa.
- Texto moral e filosófico.
- Interação via JavaScript.

### Depoimentos

Há uma seção de depoimentos estáticos com nomes e textos. Não foi encontrada origem, consentimento ou sistema editorial visível.

### Footer

- Links de navegação.
- Login.
- Cadastro.
- Vídeo externo.
- “Privacidade” com `href="#"`.
- “Termos de Uso” com `href="#"`.
- Link para voltar ao topo.

## 3.2 Conteúdo da área autenticada

### Dashboard

- Saudação ao usuário.
- Faixa atual.
- Resumo de treinos.
- Minutos treinados.
- Katas e Kihons concluídos.
- Atalhos para módulos.
- Histórico/atividade.

### Treinos

- Filtros de mês, ano e busca.
- Sugestões conforme a faixa do usuário.
- Cards de treino.
- Modal de novo/editar treino.
- Seleção de exercícios.
- Séries e repetições.
- Paginação.
- Alertas/toasts.

### Katas/Kihons

- Listas filtráveis.
- Busca.
- Níveis/categorias.
- Links para vídeos.
- Marcação de progresso.

### Progresso

- Total de treinos.
- Tempo treinado.
- Frequência.
- Histórico.
- Gráfico.
- Conquistas.
- Jornada de faixas.

### Anotações

- Criar, editar e excluir.
- Categorias e cores.
- Busca.
- Conteúdo pessoal do aluno.

### Perfil

- Nome, e-mail e nascimento.
- Faixa atual.
- Tipo de conta.
- Data de entrada.
- Foto.
- Alteração de faixa.
- Alteração de senha.
- Jornada visual de faixas.

## 3.3 Tom de voz

### Características

- Marcial e aspiracional.
- Mistura linguagem de produto com vocabulário de dojo.
- Uso de “Osu!”, “guerreiro”, “tatame”, “sensei”, “Yudansha”, “Kyu” e “Dan”.
- Forte presença de termos japoneses e kanji.
- Estética editorial/dark com acentos vermelhos e dourados.

### Pontos fortes

- Marca coerente com o tema.
- Boa diferenciação visual.
- Mensagem emocional para praticantes.
- CTAs principais são claros: registrar e acessar painel.
- Conteúdo da landing contextualiza o produto antes do cadastro.

### Pontos a melhorar

- Alguns termos podem afastar iniciantes por falta de explicação.
- “Kumite brutal”, “nocaute” e referências a fraturas podem soar agressivos ou inseguros.
- Afirmações históricas e técnicas deveriam ter fontes ou redação mais cautelosa.
- A copy ainda não informa claramente quem opera o dojo/produto.
- Falta localização, contato, suporte, política de privacidade e termos reais.
- Não há proposta explícita de benefício em linguagem simples para alguém que não conhece Kyokushin.

## 3.4 Erros e riscos de conteúdo

| Conteúdo | Achado | Gravidade |
|---|---|---|
| Footer | Privacidade e Termos apontam para `#` | Média |
| Depoimentos | Origem/consentimento não identificados | Média |
| Estatísticas | “15+ Katas” pode não refletir o seed de quatro Katas | Média |
| Equipamento | “Homologados” precisa de fonte verificável | Média |
| História | Afirmações sobre Oyama sem referências | Baixa/Média |
| Condicionamento | Recomendações físicas podem ser interpretadas como universais | Média |
| Mistura de idiomas | Termos de produto em inglês sem glossário | Baixa |
| Vídeos | Links externos podem ficar offline ou mudar | Média |

---

# 4. Experiência do Usuário e Design

## 4.1 Design e layout

### Pontos positivos

- `css/tokens.css` centraliza cores, tipografia, espaçamento, radius e sombras.
- Tema dark/light implementado.
- Uso consistente de vermelho de marca e dourado para graduação/conquistas.
- Cards com hierarquia clara.
- Landing com hero, bento cards, filtros, explorer e seções temáticas.
- Dashboard separado por módulos.
- Modais para ações importantes.
- Estados de vazio e feedback visual presentes em diversos fluxos.
- Uso de fontes display para títulos e sans para conteúdo.

### Inconsistências

1. Landing, área do aluno e admin possuem shells distintos.
2. Há navbar em `includes/navbar.php` e outra em `php/navbar.php`.
3. Há múltiplas convenções de tema: `html.light`, `html.light-mode` e `body.light-mode`.
4. Há `AppModal`, toast legado e modais específicos.
5. Existem CSS legados `registro.css` e `perfil_aluno.css` além dos arquivos atuais.
6. Algumas páginas carregam famílias tipográficas diferentes.
7. Há estilos inline e eventos `onclick` misturados com JavaScript externo.

## 4.2 Responsividade

### Recursos existentes

- Media queries em landing, navbar, dashboard e modais.
- Menu hamburger na navbar.
- Grids adaptativos em cards.
- Modal de edição de perfil com campos que se reorganizam.
- Painel de acessibilidade com limite de altura e rolagem.

### Riscos observados

- Navbar desktop contém muitos itens para notebooks estreitos.
- Painéis administrativos densos podem sofrer overflow horizontal.
- Tabelas e filtros podem não caber em 768px ou 1024px.
- Títulos grandes e fontes ampliadas podem empurrar cards.
- Alguns grids usam valores fixos e precisam ser validados em 1366x768, 1280x720, 1024x768, 768x1024 e celulares.
- Modal de treino possui muitos controles e precisa de teste com teclado e viewport pequeno.
- Imagens de equipamentos dependem da correta aplicação de `.gear-media` para alinhamento uniforme.

### Plano de testes visual recomendado

| Viewport | Cenários |
|---|---|
| 1366x768 | Navbar, hero, equipamento, faixas |
| 1280x720 | Dashboard, treinos e modal |
| 1024x768 | Navbar compacta, tabelas, admin |
| 768x1024 | Tablet portrait |
| 390x844 | Login, registro, perfil, modal |
| 320x568 | Limite mínimo, painel de acessibilidade |

## 4.3 Navegabilidade

### Pontos positivos

- Landing possui âncoras para seções.
- Navbar autenticada possui acesso aos módulos principais.
- Dashboard oferece atalhos.
- Treinos possuem filtros e paginação.
- Catálogos têm busca/filtros.

### Lacunas

- Não há busca global.
- Não há breadcrumb na área administrativa ou módulos internos.
- Não há indicação clara de localização em todas as páginas.
- Alguns links legais não levam a páginas reais.
- A recuperação de senha não conclui um fluxo real.
- Não há central de ajuda/FAQ.

## 4.4 Acessibilidade WCAG

### Existente

- `lang="pt-BR"` em páginas principais.
- Labels em formulários.
- `alt` em várias imagens.
- `aria-label` em botões importantes.
- Foco global em `tokens.css`.
- Painel de texto ampliado, contraste, redução de movimento, destaque de links e TTS.
- `aria-pressed` no painel de acessibilidade.
- Fechamento por Escape em parte dos modais.

### Problemas

1. Alguns CSS usam `outline: none`.
2. Focus trap não é completo nos modais.
3. Foco nem sempre retorna ao botão disparador.
4. Erros de formulário não estão sempre associados via `aria-describedby`.
5. Filtros não anunciam quantidade/resultados para leitor de tela.
6. TTS é limitado a 5.000 caracteres e não tem pausa/retomada/seleção.
7. `prefers-reduced-motion` não é aplicado automaticamente.
8. Tabs interativas podem não ter `tabpanel`, `aria-controls` e navegação por setas completa.
9. Canvas precisa de alternativa textual equivalente.
10. Uso de emoji como ícone pode gerar leitura inconsistente.
11. Contraste precisa ser medido em todos os estados de tema e hover.
12. O recurso VLibras foi removido; documentação e expectativas devem permanecer coerentes com isso.

---

# 5. SEO e Martech

## 5.1 SEO on-page

### Existente

- `lang="pt-BR"`.
- `viewport` nas páginas principais.
- Titles específicos em várias páginas.
- Meta descriptions em landing, login, registro, perfil, dashboard e progresso.
- H1/H2/H3 em grande parte das interfaces.
- `alt` na maioria das imagens da landing.
- URLs de arquivos compreensíveis dentro da estrutura atual.

### Ausências

Não foram encontrados:

- `rel="canonical"`;
- Open Graph (`og:title`, `og:description`, `og:image`);
- Twitter Cards;
- JSON-LD/Schema.org;
- `robots.txt`;
- `sitemap.xml`;
- `hreflang`;
- breadcrumbs estruturados;
- schema de `Organization`, `WebSite`, `Article`, `SportsActivityLocation` ou `FAQPage`.

### Problemas de indexabilidade

1. A maioria da área autenticada não deve ser indexada e não há estratégia explícita `noindex` observável.
2. Conteúdo alternado por JavaScript nas faixas e Dojo Kun pode não ser totalmente descoberto por todos os crawlers.
3. A landing possui conteúdo rico, mas não há marcação estruturada para explicar produto/organização.
4. A URL local não representa uma URL canônica de produção.
5. Não há estratégia de URLs amigáveis além das tentativas de rewrite administrativo.

## 5.2 SEO técnico

### `.htaccess`

O `.htaccess` contém regras como:

```apache
RewriteRule ^admin/katas ... admin/api/katas.php
```

Mas a aplicação real está sob `php/admin`. Em uma instalação na raiz `/Kyokushin-Karate`, a regra pode não corresponder ao caminho efetivo `/php/admin/...`.

**Ação:** decidir entre:

- expor e documentar a API em `/admin/...`;
- alterar as regras para `/php/admin/...`;
- ou remover regras não utilizadas.

### Arquivos sensíveis

Há bloqueio para `.git`, `.env`, SQL, Markdown, DOCX, logs e `config.php`. Isso é positivo, mas deve ser testado em Apache real e não substituir a remoção de segredo do código.

### Canonical e ambiente

É necessário definir:

- domínio oficial;
- versão HTTPS;
- trailing slash;
- redirecionamentos de HTTP para HTTPS;
- `www` ou não `www`;
- páginas indexáveis;
- páginas privadas `noindex`.

## 5.3 Martech e rastreamento

Não foram encontrados scripts ou IDs de:

- Google Analytics/GA4;
- Google Tag Manager;
- Meta Pixel;
- Microsoft Clarity;
- Hotjar;
- LinkedIn Insight;
- TikTok Pixel;
- eventos customizados;
- dataLayer;
- Search Console verification.

Consequentemente não há base técnica para responder quantos usuários:

- clicaram em “Registrar”;
- concluíram cadastro;
- iniciaram login;
- abandonaram login;
- usaram filtros;
- registraram treino;
- abriram modal;
- clicaram em vídeos;
- retornaram ao site.

### Plano de instrumentação recomendado

Eventos mínimos:

- `landing_cta_register_click`;
- `landing_cta_login_click`;
- `signup_started`;
- `signup_completed`;
- `login_success`;
- `login_failure` sem enviar senha ou dados sensíveis;
- `training_create_started`;
- `training_created`;
- `training_edit_started`;
- `training_deleted`;
- `kata_completed`;
- `kihon_completed`;
- `profile_edit_completed`;
- `belt_explorer_selected`;
- `content_video_click`;
- `accessibility_setting_changed`.

Não enviar para Analytics:

- senha;
- token CSRF;
- foto privada;
- conteúdo completo de anotações;
- e-mail em texto puro;
- dados de saúde.

---

# 6. Funcionalidades, Fluxos e Interatividade — O que acontece

## 6.1 Fluxo público

1. Usuário entra na landing.
2. Navega por âncoras.
3. Filtra técnicas.
4. Seleciona faixas no explorer.
5. Consulta especificações de equipamento.
6. Interage com Dojo Kun.
7. Abre vídeo externo.
8. Escolhe entrar ou registrar-se.

## 6.2 Cadastro

### Fluxo observado

1. `php/registro.php` consulta faixas.
2. Exibe nome, e-mail, nascimento, faixa e senha.
3. Envia para `php/registrar_aluno.php` por POST.
4. Valida CSRF.
5. Valida campos obrigatórios.
6. Valida e-mail.
7. Valida data.
8. Faz hash da senha.
9. Verifica duplicidade.
10. Insere usuário.
11. Redireciona para login.

### Lacunas

- Não há confirmação de e-mail.
- Não há rate limit.
- Não há CAPTCHA/anti-bot.
- Política de senha não é claramente forte no servidor.
- `faixa_id` precisa de validação referencial antes do insert.
- Não há evento de conversão mensurável.

## 6.3 Login

### Fluxo observado

1. Usuário envia e-mail/senha.
2. CSRF é validado.
3. Busca por e-mail.
4. `password_verify` confere hash.
5. Sessão é regenerada.
6. ID, nome e tipo são gravados.
7. Atividade é registrada.
8. Usuário é redirecionado ao dashboard.

### Falhas

- Usuário inativo não é bloqueado.
- Sem rate limit.
- Sem MFA.
- Sem alerta de tentativa suspeita.
- Recuperação de senha não é real.
- Não há instrumentação de sucesso/falha.

## 6.4 Treinos

### Criação

- Tela com filtros de mês/ano/busca.
- Sugestões por faixa do usuário.
- Modal de treino.
- Data, duração, observações.
- Exercícios dinâmicos.
- Séries e repetições.
- CSRF.
- Validação de data futura.

### Edição

- Verificação de propriedade.
- Atualização dos dados.
- Substituição dos exercícios.
- CSRF.

### Exclusão

- A interface direciona para URL GET.
- O endpoint aceita POST e GET.
- CSRF só é validado em POST.
- Essa é uma vulnerabilidade de integridade e CSRF.

### Integridade

O schema possui:

- `treinos.nome`;
- `treinos.descricao`;
- `treinos.nivel`;
- `treino_exercicios.exercicio_id`.

O fluxo principal utiliza principalmente descrição livre, duração, data e observações. Isso cria inconsistência entre o modelo e o produto:

- relatórios podem não conseguir agrupar por exercício real;
- renomear um exercício não atualiza históricos;
- dados ficam duplicados em texto;
- estatísticas por técnica ficam difíceis.

## 6.5 Katas/Kihons/progresso

- Catálogos consultam dados do banco.
- Busca/filtro ocorre no cliente ou servidor conforme a página.
- Usuário marca conclusão.
- `toggle_progresso.php` cria/atualiza registro.
- Chave única impede duplicidade por usuário/tipo/referência.

Problemas:

- Falta CSRF.
- `referencia_id` não é validado contra a tabela correspondente.
- Não há foreign key polimórfica possível no modelo atual.
- Não há histórico de quando cada conclusão ocorreu, além do timestamp de atualização.
- Não há auditoria de reversões.

## 6.6 Anotações

- CRUD por usuário.
- Categorias e cores.
- Busca.
- Validação CSRF.
- Prepared statements.
- Escape HTML.

Problema arquitetural: criação de tabela em cada request.

## 6.7 Perfil

- Visualização de dados.
- Upload de foto.
- Remoção de foto.
- Alteração de nome/nascimento/faixa.
- Alteração opcional de senha.
- Jornada visual de faixas.

Pontos positivos:

- CSRF no POST.
- Password hash para nova senha.
- Modal responsivo.
- Preview de foto.
- Controle de propriedade pelo usuário da sessão.

## 6.8 Administração

- Dashboard de indicadores.
- CRUD de usuários.
- CRUD de Katas.
- CRUD de Kihons.
- CRUD de exercícios.
- CRUD de faixas.
- Lista de treinos.
- Ranking/progresso.
- Configurações.
- APIs JSON.

Riscos:

- APIs mutáveis sem CSRF adequado.
- Paginação não uniforme.
- URLs recebidas podem não ter allowlist.
- Ações críticas podem não exigir confirmação/reautenticação.
- Falta trilha detalhada de auditoria de alterações.

---

# 7. A Lacuna — O que não aconteceu

## 7.1 Dados não disponíveis

Não foi possível afirmar que houve ou não houve atividade real, porque não há Analytics, logs de acesso, Search Console ou banco de produção disponível.

As seguintes perguntas permanecem sem resposta factual:

- Quantas pessoas acessaram a landing?
- Qual foi o canal de aquisição?
- Quantas clicaram em registrar?
- Quantas iniciaram o cadastro?
- Quantas concluíram o cadastro?
- Quantas tentaram login?
- Quantas falharam no login?
- Quantas voltaram ao site?
- Quais páginas foram mais vistas?
- Qual foi o tempo médio de permanência?
- Qual foi a taxa de abandono?
- Qual foi a taxa de conversão por dispositivo?
- Quais CTAs foram ignorados?
- Qual foi o caminho mais comum até um treino registrado?
- Quantos treinos foram registrados por usuário?
- Quais faixas tiveram maior atividade?
- Quais Katas/Kihons tiveram maior conclusão?
- Houve erro 404/500 em produção?
- Houve falhas de e-mail?
- Houve indisponibilidade?

## 7.2 Conversões não mensuradas

Os seguintes eventos existem visualmente, mas não são mensurados:

- CTA “Criar Conta Gratuita”.
- CTA “Acessar Meu Painel”.
- Botão “Registrar”.
- Envio do cadastro.
- Login bem-sucedido.
- Início de treino.
- Conclusão de treino.
- Conclusão de Kata/Kihon.
- Edição de perfil.
- Seleção de faixa.
- Clique em vídeo.

Não é possível dizer que foram ignorados; apenas que não há dados para saber.

## 7.3 Pontos prováveis de abandono

São hipóteses que precisam de Analytics/session recording para confirmação:

1. Cadastro: quantidade de campos e senha podem gerar desistência.
2. Login: recuperação de senha simulada pode bloquear usuários.
3. Modal de treino: muitos campos podem elevar esforço cognitivo.
4. Área de treinos: seleção dinâmica de exercícios pode ser longa.
5. Catálogos: vídeos externos podem interromper a jornada.
6. Perfil: alteração de faixa e edição dependem de modais.
7. Mobile: navbar, filtros e tabelas podem gerar overflow.
8. Acessibilidade: o painel tem múltiplos controles e pode ficar denso.

## 7.4 Elementos ausentes

- Recuperação de senha funcional.
- Verificação de e-mail.
- FAQ.
- Contato/suporte claro.
- Política de privacidade funcional.
- Termos de uso funcional.
- Consentimento de cookies, se aplicável ao rastreamento.
- Localização/identidade do dojo ou organização.
- Prova social verificável.
- Perfil de instrutor/sensei.
- Calendário de treinos.
- Exportação de dados.
- Histórico de graduação com datas.
- Sistema de notificação.
- Histórico de auditoria visível para admin.
- Integração de Analytics.
- Sitemap/robots/canonical.
- Schema.org.
- Testes automatizados.
- Monitoramento de erros.
- Health check.

## 7.5 Oportunidades de conteúdo e SEO

Palavras-chave/opções de conteúdo a considerar, sem afirmar volume de busca:

- treino de Kyokushin para iniciantes;
- Kihon Kyokushin passo a passo;
- Katas Kyokushin por faixa;
- Taikyoku Sono Ichi tutorial;
- Pinan Sono Ichi técnica;
- Mae Geri Kyokushin;
- Mawashi Geri Kyokushin;
- preparação para exame de faixa;
- diferença entre Kyu e Dan;
- equipamentos para Kyokushin;
- treino de kumite com segurança;
- Dojo Kun significado;
- diário de treino de karate;
- acompanhamento de progresso no karate.

Antes de produzir conteúdo, validar intenção e volume em Keyword Planner, Search Console ou ferramenta equivalente.

---

# 8. Relatório de Erros e Inconsistências

| Elemento/Página | Tipo de Problema | Gravidade | Descrição do Ocorrido |
|---|---|---|---|
| `php/config.php` | Segurança | Alta/Crítica | Usuário e senha de banco estão diretamente no código. Recomenda-se rotação imediata. |
| `php/Database.php` | Arquitetura/Segurança | Alta | Repete dependência de credencial e mantém segunda camada de conexão. |
| `php/login.php` | Autorização | Alta | Consulta usuário apenas por e-mail e não verifica `ativo = 1`. |
| `php/login.php` | Funcionalidade | Alta | Recuperação de senha apenas simula envio; não existe fluxo real. |
| `dashboard/deletar_treino.php` | CSRF/Integridade | Alta | GET pode excluir treino; token só é validado em POST. |
| `dashboard/toggle_progresso.php` | CSRF | Alta | POST mutável não chama `validar_csrf()`. |
| `php/admin/api/*.php` | CSRF | Alta | APIs mutáveis não têm proteção CSRF centralizada observável. |
| `dashboard/foto_perfil.php` | Upload | Média/Alta | Fallback pode salvar original e não há regra específica de execução em `uploads/perfil`. |
| `php/setup_db.php` | Infra/Security | Alta | Script de setup/migração precisa ser restrito a CLI ou admin protegido. |
| Sessões PHP | Segurança | Média/Alta | Cookies e política de sessão não são configurados de forma centralizada. |
| `php/logout.php`/admin | HTTP/UX | Média | Logout deve ser padronizado por POST e CSRF. |
| `.htaccess` | Roteamento | Média | Regras `^admin/...` podem não coincidir com a instalação em `/php/admin/...`. |
| `.htaccess` | CSP | Média | Política ainda permite domínio VLibras removido e usa `unsafe-inline`. |
| `dashboard/anotacoes.php` | Banco/Performance | Média | `CREATE TABLE IF NOT EXISTS` executado durante cada acesso. |
| `dashboard/registrar_treino.php` | Integridade | Média | Treino e exercícios não estão em transação única. |
| `dashboard/atualizar_treino.php` | Integridade | Média | Exclusão/reinserção de exercícios pode deixar estado parcial em falha. |
| `dashboard/treinos.php` | Modelo de dados | Média | Fluxo salva descrição textual em vez de preencher `exercicio_id`. |
| `database/schema.sql` | Consistência | Média | Modelo possui campos mais ricos que o fluxo principal usa. |
| `sql/sql.sql` | Consistência | Média | Há segunda definição SQL, elevando risco de divergência. |
| `php/dashboard.php` | Performance | Média | Várias consultas separadas e consultas por mês para gráfico. |
| `dashboard/progresso.php` | Performance | Média | Múltiplas consultas independentes para indicadores. |
| Admin APIs | Performance | Média | Paginação não é uniforme; algumas listagens podem retornar tudo. |
| `includes/navbar.php`/`php/navbar.php` | Arquitetura | Média | Duas navbars podem divergir em links, tema e acessibilidade. |
| Modais/toasts | Arquitetura | Média | `AppModal`, toast legado e modais inline coexistem. |
| CSS diversos | Acessibilidade | Média | Regras `outline: none` podem remover foco útil. |
| Modais | Acessibilidade | Média | Focus trap, retorno de foco e semântica não são uniformes. |
| Tabs/filtros | Acessibilidade | Média | Semântica de tab/tabpanel e anúncios de resultados precisa ser revisada. |
| `index.html` | SEO | Média | Não foram encontrados canonical, OG, Twitter Cards, JSON-LD. |
| Raiz pública | SEO | Média | Não foram encontrados `robots.txt` e `sitemap.xml`. |
| Footer `index.html` | Conteúdo/UX | Média | Privacidade e Termos apontam para `#`. |
| Depoimentos | Conteúdo/Legal | Média | Origem e consentimento dos depoimentos não estão documentados. |
| Landing | Conteúdo | Média | Estatísticas e afirmações históricas/técnicas não possuem fonte visível. |
| `index.html`/catálogos | Conteúdo | Baixa/Média | Dependência de vídeos/URLs externas sem fallback editorial. |
| Fontes externas | Performance/Privacidade | Média | Muitas famílias/pesos Google Fonts aumentam dependência de terceiros. |
| Analytics | Dados | Alta para gestão | Nenhum sistema de tracking foi encontrado; conversões e funil são invisíveis. |
| Search Console | SEO | Média | Não há evidência de verificação ou dados de busca orgânica. |
| Web Vitals | Observabilidade | Média | Não há medição real de LCP, INP, CLS, FCP ou TTFB. |
| Testes | Qualidade | Média/Alta | Não foram encontrados testes automatizados de segurança, integração ou UI. |
| Admin seed | Segurança | Média | `admin@admin.com` é identificador previsível. |
| URLs administrativas | Infra | Média | Rotas limpas e caminhos reais parecem desalinhados. |
| URLs de vídeo/imagem | Segurança | Média | APIs devem validar esquema/domínio permitido antes da renderização. |
| `login.php` | Segurança | Média | Não há rate limiting ou bloqueio progressivo. |
| Conteúdo físico | Editorial/Safety | Média | Recomendações de condicionamento e combate precisam de contexto e segurança. |

---

# 9. Lista Cheia para Relatório Executivo

## Prioridade P0 — corrigir imediatamente

- Rotacionar a senha do banco exposta.
- Retirar credenciais do código e do histórico versionado.
- Criar usuário de banco dedicado com privilégios mínimos.
- Bloquear execução web de `setup_db.php` ou removê-lo da raiz pública.
- Exigir POST + CSRF para exclusão de treino.
- Adicionar CSRF ao toggle de progresso.
- Aplicar CSRF às APIs administrativas mutáveis.
- Bloquear login de usuários `ativo = 0`.
- Restringir uploads e bloquear execução em `uploads/perfil`.
- Validar URLs de vídeo/imagem por HTTPS e allowlist.

## Prioridade P1 — corrigir antes de produção

- Implementar recuperação de senha real.
- Implementar rate limiting para login e endpoints sensíveis.
- Centralizar configuração de sessão.
- Padronizar `require_login()`/`require_admin()`.
- Adicionar transações em criação/edição de treino.
- Validar `referencia_id` de Katas/Kihons.
- Consolidar mysqli/PDO em uma camada.
- Mover migração de anotações para schema/migração.
- Unificar navbar.
- Unificar modal/toast.
- Corrigir rotas do `.htaccess`.
- Criar logs de auditoria administrativos.
- Adicionar confirmações/reautenticação para ações destrutivas.

## Prioridade P2 — mensuração e produto

- Instalar GA4 ou solução equivalente com consentimento apropriado.
- Definir eventos de funil.
- Configurar Search Console.
- Criar dashboard de conversão.
- Medir Web Vitals em desktop e mobile.
- Monitorar 404/500/JS errors.
- Criar health check de Apache/PHP/MySQL.
- Medir cadastro, login, treino criado e retenção.
- Criar histórico de graduação.
- Adicionar metas e calendário.
- Adicionar exportação de dados.
- Adicionar suporte/FAQ.

## Prioridade P3 — SEO e conteúdo

- Definir domínio canônico.
- Adicionar canonical.
- Adicionar Open Graph e Twitter Cards.
- Criar `robots.txt`.
- Criar `sitemap.xml`.
- Adicionar JSON-LD.
- Marcar área privada com `noindex` quando apropriado.
- Criar páginas reais de privacidade e termos.
- Documentar fontes históricas e técnicas.
- Revisar claims de equipamento e homologação.
- Identificar depoimentos fictícios ou coletar consentimento.
- Criar glossário de termos japoneses.
- Produzir conteúdos por faixa e intenção de busca.

## Prioridade P4 — UX/UI e acessibilidade

- Testar 1366x768, 1280x720, 1024x768, tablet e celulares.
- Remover `outline: none` ou substituir por foco equivalente.
- Implementar focus trap completo nos modais.
- Restaurar foco ao elemento disparador.
- Associar mensagens de erro aos campos.
- Aplicar `prefers-reduced-motion`.
- Dar alternativa textual ao gráfico.
- Melhorar anúncios de resultados de filtros.
- Validar contraste WCAG AA.
- Reduzir fontes externas e pesos não usados.
- Melhorar estado de carregamento dos botões.
- Rever tabelas administrativas em telas pequenas.

## Checklist de aceite para a próxima versão

- [ ] Nenhuma credencial real no repositório.
- [ ] Usuário inativo não entra.
- [ ] Nenhuma operação destrutiva mutável por GET.
- [ ] Todos os POST/PUT/PATCH/DELETE validam CSRF.
- [ ] Upload não executa conteúdo e possui limite/quota.
- [ ] Recuperação de senha funciona de ponta a ponta.
- [ ] Sessão usa cookies endurecidos.
- [ ] APIs administrativas paginam e validam entrada.
- [ ] Treino e exercícios usam transação.
- [ ] `robots.txt`, `sitemap.xml`, canonical e OG publicados.
- [ ] GA4/Analytics configurado com eventos mínimos.
- [ ] Search Console configurado.
- [ ] Web Vitals medidos em desktop e mobile.
- [ ] Testes automatizados de autenticação, CSRF e isolamento de usuário.
- [ ] Teste visual em notebooks pequenos e smartphones.
- [ ] Teste de teclado e leitor de tela.
- [ ] Privacidade e termos possuem páginas reais.
- [ ] Depoimentos e claims possuem origem/documentação.

---

## Conclusão

O Oyama Hub tem uma base funcional e visual promissora, mas ainda deve ser tratado como aplicação em evolução, não como sistema pronto para exposição pública sem revisão. A análise estática indica que o maior retorno virá de corrigir segurança e integridade de dados, instalar observabilidade e finalizar fluxos incompletos. Só depois da instalação de Analytics, logs e medição de Web Vitals será possível responder com rigor o que aconteceu com usuários, conversões, abandono e desempenho real.

Este documento não afirma tráfego, conversões ou falhas históricas que não estejam registradas no workspace. Onde faltam dados, a ausência foi explicitamente marcada como lacuna de observabilidade.
