# Oyama Hub

Plataforma web para praticantes de Karate Kyokushin organizarem treinos, estudarem Katas e Kihons e acompanharem a evolução por graduação.

## Estrutura

- `index.html`: landing page pública.
- `php/`: entrypoints públicos, autenticação, infraestrutura e área administrativa.
- `dashboard/`: páginas e handlers compatíveis da área do aluno.
- `includes/`: componentes PHP compartilhados.
- `css/`, `js/`, `img/`: assets públicos organizados por responsabilidade.
- `database/`: schema canônico e migrations incrementais.
- `uploads/`: arquivos de usuário; não contém código executável.
- `tests/`: smoke tests executáveis localmente.
- `docs/`: auditorias, requisitos e documentação operacional.
- `legacy/`: arquivos históricos sem referências ativas, mantidos para consulta.

## Configuração local

1. Crie `.env` baseado em `.env.example`.
2. Configure o usuário do MySQL e o banco `oyama_hub`.
3. Execute `database/schema.sql` em uma instalação nova.
4. Para banco existente, aplique as migrations em `database/migrations/`.
5. Aponte o Apache/XAMPP para a raiz do projeto e confirme `mod_rewrite` quando necessário.

O setup web exige sessão de administrador e método POST. Para produção, prefira executar migrações via CLI/deploy controlado.

## Testes

No PowerShell:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
.\tests\smoke.ps1
```

O smoke test valida sintaxe PHP/JavaScript, arquivos críticos, schema canônico e ausência de credenciais hardcoded.

## Convenções

- Use `require_once __DIR__ . '/...'` para dependências PHP internas.
- `database/schema.sql` é a fonte oficial para novas instalações.
- `sql/` não é mais usado para migrações operacionais.
- URLs públicas existentes são mantidas por compatibilidade; reorganizações futuras devem usar wrappers ou redirects testados.

## Documentação

Consulte [docs/architecture.md](docs/architecture.md), [docs/deployment.md](docs/deployment.md) e [docs/AUDITORIA_WEB_COMPLETA.md](docs/AUDITORIA_WEB_COMPLETA.md).
