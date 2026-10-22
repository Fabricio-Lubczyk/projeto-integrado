# Desafio Integrador

Estrutura inicial do projeto em **PHP com Laravel 12**, com a página de boas-vindas padrão em Blade e configuração de exemplo para PostgreSQL.

## Requisitos

- PHP 8.2 ou superior, compatível com Laravel 12.
- Composer 2.
- Node.js 22.12 ou superior e npm.
- Extensão PHP `pdo_pgsql` para conectar ao PostgreSQL.

## Executar localmente

Na raiz do projeto, em PowerShell:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm ci
npm run build
php artisan serve
```

Acesse <http://localhost:8000>. Se o arquivo `.env` já existir, mantenha suas configurações em vez de copiá-lo novamente.

Para desenvolver os arquivos CSS e JavaScript, execute `npm run dev` em outro terminal.

Sessões e cache usam arquivos, e a fila está configurada como síncrona. Isso permite abrir a página inicial antes de configurar o banco.

## PostgreSQL / Neon

Edite as variáveis `DB_*` no arquivo `.env` com os dados do banco. Para o Neon, use o host, nome de banco e usuário fornecidos pelo serviço e defina `DB_SSLMODE=require`.

Depois de configurar a conexão, execute as migrations padrão:

```powershell
php artisan migrate
```

As credenciais ficam somente no `.env`, que é ignorado pelo Git. O arquivo `.env.example` contém apenas valores de exemplo.

## Testes

```powershell
php artisan test
```

Nesta base, os testes de exemplo verificam a estrutura inicial e a resposta da página de boas-vindas. A configuração de testes mantém SQLite em memória; a integração PostgreSQL será acrescentada quando as funcionalidades do projeto forem desenvolvidas.

## Estrutura principal

```text
app/                  Controllers, models e providers
bootstrap/            Inicialização da aplicação
config/               Configurações
database/             Migrations, factories e seeders
public/               Entrada pública da aplicação
resources/            Views Blade, CSS e JavaScript
routes/               Rotas web e comandos de console
storage/              Cache, sessões, arquivos e logs
tests/                Testes automatizados
```

Esta entrega contém somente a base do Laravel. Os módulos de negócio serão definidos nas próximas etapas.
