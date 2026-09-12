# Deploy e operação

## Ambiente

Configure `.env` fora do controle de versão usando `.env.example` como referência. O usuário do banco deve ter apenas as permissões necessárias para a aplicação.

## Banco

- Instalação nova: execute `database/schema.sql`.
- Instalação existente: aplique as migrations em ordem.
- Não use `sql/schema.sql`; ele foi arquivado como versão histórica.

## Apache/XAMPP

Ative PHP, MySQL/MariaDB, `mod_rewrite`, `mod_headers`, `mod_expires` e `mod_deflate`. Verifique o `.htaccess` em ambiente real antes de publicar.

## Migrações

O endpoint `php/setup_db.php` aceita CLI ou POST autenticado por administrador com CSRF. Em produção, prefira CLI ou pipeline de deploy.

## Recuperação de senha

O envio depende de `MAIL_FROM` e da configuração de envio do PHP no servidor. Sem um MTA/SMTP configurado, o token é armazenado, mas o usuário não receberá o link.
