# Projeto Integrado — Catálogo de Jogos

Aplicação web desenvolvida para o projeto integrador da faculdade. A proposta é criar um espaço onde jogadores possam descobrir jogos e compartilhar suas experiências por meio de avaliações.

Nesta etapa, o projeto oferece contas de usuários, administração de jogos e um catálogo público com gêneros e plataformas. As avaliações ainda serão implementadas.

## Funcionalidades disponíveis

- Cadastro, login e logout de usuários.
- Consulta e edição dos dados da conta e alteração de senha.
- Área administrativa protegida, acessível apenas a administradores.
- Cadastro de jogos com título, desenvolvedora, descrição e data de lançamento opcional.
- Associação de vários gêneros e plataformas a cada jogo.
- Catálogo público com busca por título, filtros combinados por gênero e plataforma e paginação que preserva os filtros.
- Exibição de gêneros nos cartões e de gêneros e plataformas nos detalhes de cada jogo.
- Formulário que preserva os dados e as opções selecionadas quando há erro de validação.
- Layout adaptado para celular e computador.

## Tecnologias

- PHP e Laravel 12.
- Blade para as páginas renderizadas no servidor.
- CSS e JavaScript, com Vite para compilar os arquivos do frontend.
- SQLite para o banco de dados local.
- Eloquent para os modelos e relacionamentos.
- PHPUnit para os testes automatizados e Laravel Pint para formatação do PHP.

A configuração do PostgreSQL no Neon está prevista para uma etapa futura. O fluxo local descrito abaixo utiliza SQLite.

## Requisitos

- PHP 8.2 ou superior, com as extensões exigidas pelo Composer e o driver `pdo_sqlite` habilitado.
- Composer 2.
- Node.js 22.12 ou superior e npm.
- Git.

## Instalação local

Clone o repositório e entre na pasta:

```bash
git clone https://github.com/Fabricio-Lubczyk/projeto-integrado.git
cd projeto-integrado
```

Para acessar a implementação de gêneros e plataformas desta entrega:

```bash
git switch feat/generos-plataformas
```

Instale as dependências e prepare a configuração local:

```bash
composer install
php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
```

O `.env.example` já utiliza `DB_CONNECTION=sqlite`. Crie o arquivo do banco, execute as migrations e carregue os gêneros e plataformas de exemplo:

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
php artisan db:seed --class=GenreSeeder
php artisan db:seed --class=PlatformSeeder
```

Esses dois seeders podem ser executados novamente sem duplicar as opções. Eles incluem Ação, Aventura, Estratégia, Esporte e RPG, além de PC, PlayStation, Xbox e Nintendo Switch. Jogos devem ser cadastrados pela administração.

Instale as dependências do frontend e compile os arquivos:

```bash
npm ci
npm run build
```

Inicie a aplicação:

```bash
php artisan serve
```

Acesse <http://localhost:8000>. Durante o desenvolvimento, você também pode usar `composer run dev` para executar o servidor Laravel e o Vite juntos, com atualização dos arquivos do frontend.

## Acesso à administração

Crie uma conta pela interface de cadastro. Novas contas são de jogadores e não recebem acesso administrativo automaticamente.

Para promover uma conta no seu ambiente local, abra o Tinker:

```bash
php artisan tinker
```

Execute o código abaixo, substituindo o e-mail pelo da conta cadastrada:

```php
$user = App\Models\User::where('email', 'seu-email@exemplo.com')->firstOrFail();
$user->is_admin = true;
$user->save();
```

Saia com `exit` e acesse `/admin`. O formulário de cadastro de jogos fica em `/admin/jogos/novo`.

## Organização dos dados

Cada jogo pode ter vários gêneros e estar disponível em várias plataformas. Os relacionamentos são muitos para muitos:

- `games`: dados dos jogos.
- `genres`: nomes únicos dos gêneros.
- `platforms`: nomes únicos das plataformas.
- `game_genre`: vínculos entre jogos e gêneros.
- `game_platform`: vínculos entre jogos e plataformas.

As tabelas intermediárias usam chaves estrangeiras e impedem vínculos duplicados. O cadastro do jogo e de seus vínculos acontece em uma transação. No catálogo, as relações são carregadas antecipadamente para evitar consultas individuais para cada jogo.

## Testes e verificações

Com o ambiente configurado, execute a suíte completa:

```bash
composer test
```

Os testes usam SQLite em memória, conforme `phpunit.xml`, e cobrem autenticação, conta, permissões administrativas, cadastro e exibição de jogos, vínculos, filtros combinados, paginação e dados inválidos. Também verificam a repetição dos seeders, o rollback em caso de falha no cadastro e a quantidade de consultas do catálogo.

Para executar apenas os testes de gêneros e plataformas:

```bash
php artisan test --filter=GameTaxonomyTest
```

Para verificar a formatação PHP e compilar o frontend:

```bash
vendor/bin/pint --test
npm run build
```

Se aparecer `could not find driver` ao acessar o banco ou executar os testes, confira se a extensão `pdo_sqlite` está habilitada no PHP usado pelo terminal.

## Próximas etapas

- Implementar avaliações feitas pelos jogadores.
- Configurar PostgreSQL no Neon com o grupo.

Essas funcionalidades ainda não fazem parte desta entrega. O projeto utiliza páginas Blade e não implementa uma API nesta etapa.
