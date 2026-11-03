# PlayNota

Site de consulta e avaliação de jogos, desenvolvido para as disciplinas de Banco de Dados II e Back-end.

**Site:** https://playnota.vercel.app
**Branch principal:** `main`
**Integrantes:** Fabrício, Wesley, Eduardo e Kawan.

## O que o sistema faz

- Cadastro, login, logout, edição da conta e troca de senha.
- Catálogo com busca, filtros de gênero e plataforma e paginação.
- Página inicial com carrossel, categorias e jogos bem avaliados.
- Detalhes dos jogos com capas, imagens, informações e avaliações.
- Uma avaliação por usuário e jogo, com nota de 1 a 5 e comentário. O autor pode editar ou excluir.
- Administração com cadastro, edição e exclusão de jogos com confirmação.
- API JSON com CRUD de jogos.
- Integração com PostgreSQL no Neon e importação de informações públicas da Steam.

A base de demonstração foi preparada com 100 jogos reais, 800 contas e 5.000 avaliações. As contas e avaliações são fictícias; as avaliações de demonstração são identificadas no site. Esses dados completos estão nos arquivos SQL e de backup da entrega, e não nos seeders comuns.

## Tecnologias

Laravel 12, PHP 8.2 ou superior compatível, Blade, Eloquent, PostgreSQL, Vite, CSS, JavaScript e PHPUnit. Na Vercel, a aplicação usa PHP 8.4 com FrankenPHP em container. SQLite é usado na suíte geral de testes e pode ser usado para desenvolvimento local.

## Executar localmente

São necessários Composer 2, Node.js 22.12 ou superior, npm e as extensões PHP exigidas pelo Laravel. Para o Neon, habilite `pdo_pgsql`; para os testes gerais, `pdo_sqlite`.

```bash
git clone https://github.com/Fabricio-Lubczyk/projeto-integrado.git
cd projeto-integrado
git switch main
composer install
npm ci
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
```

O `.env.example` utiliza SQLite como configuração local inicial. Para conectar ao Neon, ajuste o `.env` com os dados privados do seu banco:

```dotenv
APP_NAME=PlayNota
APP_URL=http://localhost:8000
DB_CONNECTION=pgsql
DB_URL="postgresql://USUARIO:SENHA@HOST/BANCO?sslmode=require"
DB_SSLMODE=require
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
```

Use as credenciais reais somente no ambiente privado. Se a biblioteca PostgreSQL local exigir a identificação explícita do endpoint Neon, configure também `DB_NEON_ENDPOINT=ep-SEU-ENDPOINT`. Bibliotecas atuais normalmente usam o hostname da conexão.

Em um banco vazio, crie as tabelas e os objetos pelas migrations:

```bash
php artisan migrate
php artisan db:seed --class=GenreSeeder
php artisan db:seed --class=PlatformSeeder
npm run build
php artisan serve
```

Acesse http://localhost:8000. Para trabalhar no CSS e JavaScript com atualização automática, execute `npm run dev` em outro terminal.

Para usar a base completa de demonstração, execute o SQL ou restaure o backup em um banco vazio, em vez de criar e preencher as mesmas tabelas novamente. O script completo também registra as migrations já aplicadas.

Caso prefira SQLite no desenvolvimento, mantenha `DB_CONNECTION=sqlite`, crie `database/database.sqlite` e execute as migrations. Funções, procedimentos e gatilhos específicos do PostgreSQL não são criados nesse modo.

## Administração e importação de jogos

Novas contas são de jogadores. Para conceder acesso administrativo a uma conta no seu ambiente, use `php artisan tinker`:

```php
$user = App\Models\User::where('email', 'seu-email@exemplo.com')->firstOrFail();
$user->is_admin = true;
$user->save();
```

O painel fica em `/admin`. Para importar ou atualizar informações de jogos pela Steam, informe os respectivos App IDs:

```bash
php artisan games:importar-steam 730 570
```

A importação depende da disponibilidade da Steam e atualiza os registros já vinculados ao mesmo App ID.

## Principais rotas web

| Método | Rota | Finalidade |
| --- | --- | --- |
| GET | `/` | Destaques e categorias |
| GET | `/jogos?q=portal&genre=1&platform=1` | Catálogo e filtros; use IDs existentes |
| GET | `/jogos/{game}` | Detalhes e avaliações |
| GET / POST | `/cadastro` | Criar conta |
| GET / POST | `/entrar` | Login |
| POST | `/sair` | Logout |
| GET | `/conta` | Dados e avaliações da conta |
| GET / PATCH | `/conta/editar` / `/conta` | Formulário e atualização da conta |
| GET / PUT | `/conta/senha` | Formulário e alteração da senha |
| POST | `/jogos/{game}/avaliacoes` | Publicar avaliação |
| GET | `/avaliacoes/{review}/editar` | Editar a própria avaliação |
| PUT / DELETE | `/avaliacoes/{review}` | Atualizar ou excluir a própria avaliação |
| GET | `/admin/jogos` | Gerenciar jogos |
| GET / POST | `/admin/jogos/novo` / `/admin/jogos` | Formulário e cadastro de jogo |
| GET / PUT | `/admin/jogos/{game}/editar` / `/admin/jogos/{game}` | Formulário e atualização de jogo |
| GET / DELETE | `/admin/jogos/{game}/excluir` / `/admin/jogos/{game}` | Confirmação e exclusão de jogo |

## API de jogos

| Método | Rota | Resposta de sucesso |
| --- | --- | --- |
| GET | `/api/games` | 200, coleção paginada |
| GET | `/api/games/{game}` | 200, dados do jogo |
| POST | `/api/games` | 201, jogo criado |
| PUT | `/api/games/{game}` | 200, jogo atualizado |
| DELETE | `/api/games/{game}` | 204, sem conteúdo |

Exemplo de consulta:

```bash
curl -H "Accept: application/json" https://playnota.vercel.app/api/games/1
```

Exemplo de corpo para criação ou atualização:

```json
{
  "title": "Jogo de exemplo",
  "developer": "Estudio de exemplo",
  "released_on": "2026-11-01",
  "description": "Descricao do jogo para demonstracao.",
  "genres": [],
  "platforms": []
}
```

O título deve ser único. Os arrays podem receber IDs existentes de gêneros e plataformas. As respostas usam `GameResource`, incluindo informações dos jogos, imagens e relacionamentos. Dados inválidos retornam 422 e registros inexistentes retornam 404.

As consultas são públicas. POST, PUT e DELETE exigem sessão autenticada de administrador e token CSRF válido. A API está no grupo web do Laravel e não usa autenticação por token. Para testar a escrita com Postman ou outro cliente, preserve os cookies obtidos no login e envie o token do formulário em `_token` ou no cabeçalho `X-CSRF-TOKEN`, junto de `Accept: application/json`.

## Banco de dados

As tabelas principais são `users`, `games`, `genres`, `platforms` e `reviews`. `game_genre` e `game_platform` representam os vínculos muitos para muitos. Chaves estrangeiras e restrições únicas evitam vínculos repetidos e mais de uma avaliação por usuário e jogo.

Objetos implementados nas migrations de PostgreSQL:

- Índices `avaliacoes_jogo_data_idx` e `avaliacoes_usuario_data_idx`: apoiam as consultas de avaliações por jogo ou usuário e data.
- Funções `contar_avaliacoes_jogo` e `media_avaliacoes_jogo`: chamadas pelo `ReviewService` para consultar o resumo das avaliações.
- Procedimentos `atualizar_propria_avaliacao` e `excluir_propria_avaliacao`: usados pelo serviço para alterar ou excluir somente a avaliação do autor informado.
- Gatilho `validar_dados_avaliacao`: valida nota entre 1 e 5 e comentário preenchido com até 2.000 caracteres.
- Gatilho `atualizar_data_avaliacao_trigger`: atualiza a data de alteração.

## Testes

```bash
php artisan test
php artisan test --filter=GameApiTest
php artisan test --filter=HomeDiscoveryTest
npm run build
```

O `phpunit.xml` configura SQLite em memória, sessões e cache em memória e `DB_URL` vazio. A suíte cobre contas, permissões, CRUD de jogos, filtros, avaliações, API, importação Steam e página inicial. Na conferência de 03/11/2026 foram aprovados 101 testes, com 767 asserções; dois testes de PostgreSQL foram ignorados no SQLite.

Para executar `PostgresReviewOperationsTest`, utilize um banco PostgreSQL vazio e exclusivo para testes. Configure `DB_CONNECTION=pgsql`, `DB_DATABASE`, `DB_HOST`, `DB_USERNAME`, `DB_PASSWORD` e SSL no ambiente de teste, mantenha `DB_URL` vazio e execute:

```bash
php artisan test --filter=PostgresReviewOperationsTest
```

As variáveis definidas no processo têm precedência sobre os valores padrão do XML. O teste usa `RefreshDatabase` e pode recriar tabelas. Nunca aponte esse ambiente para o banco da apresentação ou de produção.

## SQL, backup e restauração

O pacote separado da entrega contém:

- `01_criar_playnota.sql`: estrutura, dados e objetos PostgreSQL.
- `02_playnota.backup`: backup custom, restaurado com `pg_restore`.
- `03_backup_restauracao.ps1`: execução de backup e restauração pelas ferramentas PostgreSQL.

Use ferramentas PostgreSQL 18 ou compatíveis com a versão do backup e um banco de destino vazio. O script PowerShell pede a senha de forma interativa. O SQL completo e a restauração do backup são alternativas para reproduzir a base; não aplique os dois sobre as mesmas tabelas preenchidas. A cópia foi restaurada e conferida em um banco separado no Neon.

## Publicação na Vercel

`Dockerfile.vercel` monta o PHP com FrankenPHP, instala as dependências de produção e compila a interface. `Caddyfile` limita a raiz pública à pasta `public`. Os arquivos de ambiente, backups, caches locais e documentos são excluídos do upload e da imagem.

No projeto Vercel, cadastre `APP_KEY` e `DB_URL` como Secrets de produção. Configure `APP_URL` com a URL pública e, se necessário, `DB_NEON_ENDPOINT`. Nunca use o prefixo `VITE_` para credenciais: variáveis com esse prefixo podem entrar no JavaScript público. O container usa sessões e cache no PostgreSQL e logs na saída padrão.

Com a CLI da Vercel instalada e o projeto vinculado à sua conta:

```bash
vercel deploy --prod
```

A publicação não executa migrations automaticamente. A estrutura do banco deve estar preparada antes. Para atualizações, execute migrations de forma controlada sobre o banco correto. Em produção, o debug fica desativado e o Laravel reconhece o HTTPS encaminhado pela Vercel.
