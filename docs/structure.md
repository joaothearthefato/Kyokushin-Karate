# Mapa de estrutura

## Antes

```text
index.html
php/                 entrypoints, autenticação, infraestrutura e admin
php/admin/           telas administrativas
php/admin/api/       APIs administrativas
dashboard/           telas e handlers do aluno
includes/            componentes PHP compartilhados
css/                 tokens, landing, dashboard, admin e módulos
js/                  scripts compartilhados e landing
img/                 imagens públicas
database/            schema canônico e migrations
sql/                 schema histórico duplicado
uploads/              uploads de perfil
tests/               smoke test
documentação na raiz
```

## Depois

```text
.
├── index.html                 # entrada pública preservada
├── php/                       # entrypoints compatíveis e backend atual
├── dashboard/                 # área do aluno preservada
├── includes/                 # componentes compartilhados
├── css/ js/ img/              # assets ativos preservados
├── database/                  # schema e migrations oficiais
├── uploads/                   # storage público restrito
├── tests/                     # testes
├── docs/                      # auditorias, arquitetura, deploy e referências
├── legacy/                    # arquivos sem referências ativas
├── README.md
├── .env.example
└── .gitignore
```

## Arquivos movidos

```text
AUDITORIA_WEB_COMPLETA.md -> docs/AUDITORIA_WEB_COMPLETA.md
RequisitosSites.docx      -> docs/reference/RequisitosSites.docx
php/navbar.php             -> legacy/php/navbar.php
css/perfil_aluno.css      -> legacy/css/perfil_aluno.css
css/registro.css           -> legacy/css/registro.css
sql/schema.sql             -> legacy/sql/schema.v2.5.sql
```

## Decisões de compatibilidade

Não foram movidos `php/`, `dashboard/`, `includes/`, `css/`, `js/` ou `img/`: seus caminhos aparecem em HTML, PHP, JavaScript, CSS, redirects, formulários e URLs públicas. Mover esses grupos exige wrappers/rewrite e testes HTTP em Apache real.

A próxima etapa natural é criar uma camada `app/` atrás dos entrypoints existentes, começando por serviços de autenticação, treinos e notas, sem alterar as URLs públicas.
