# Arquitetura atual

## Limite de compatibilidade

O projeto usa PHP procedural e URLs baseadas em arquivos. A raiz continua sendo o webroot do XAMPP para preservar links como `/php/login.php` e `/dashboard/treinos.php`.

## Camadas

- **Entrada pública:** `index.html`, `php/login.php`, `php/registro.php` e recuperação de senha.
- **Infraestrutura:** `php/config.php`, `php/Database.php`, `php/session.php`, `php/csrf.php` e `php/auth_check.php`.
- **Aluno:** `php/dashboard.php` e `dashboard/`.
- **Admin:** `php/admin/` e `php/admin/api/`.
- **Componentes:** `includes/`, `js/app-modal.js` e tokens CSS.
- **Dados:** `database/schema.sql` e `database/migrations/`.
- **Storage:** `uploads/` com bloqueio de execução.

## Dependências centrais

```text
session.php
├── auth_check.php
└── csrf.php

config.php
└── Database.php

php/admin/header.php
├── config.php
├── auth_check.php
└── csrf.php

php/admin/api/bootstrap.php
├── config.php
├── auth_check.php
└── csrf.php
```

## Fluxos

- Autenticação: `login.php` -> sessão -> `auth_check.php`.
- Cadastro: `registro.php` -> `registrar_aluno.php`.
- Recuperação: `solicitar_recuperacao.php` -> token -> `redefinir_senha.php`.
- Treinos: páginas em `dashboard/` e handlers POST no mesmo diretório.
- Administração: telas em `php/admin/` consomem `php/admin/api/`.

## Legado

Arquivos sem referências ativas foram movidos para `legacy/`. Eles não participam da execução atual e devem permanecer arquivados até a próxima revisão.
