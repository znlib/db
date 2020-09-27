# Конфигурация менеджера соединений

## Обявление файла конфигурации в ENV

Откройте файл `.env`.

Добавьте переменные окружения

```dotenv
DB_CONNECTION=mysql #DB_CONNECTION | DB_DRIVER
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USERNAME=root
DB_PASSWORD=
DB_DATABASE=symfony-tpl # DB_DATABASE | DB_DBNAME
DB_CHARSET=utf8
#DB_DEFAULT_DEFAULT_SCHEMA=public
```

Если каратко, то:

```dotenv
DB_CONNECTION=mysql
DB_USERNAME=root
DB_PASSWORD=qwerty
DB_DATABASE=symfony-tpl
```

Работает и форма записи Doctrine:

```dotenv
DATABASE_URL=mysql://root@127.0.0.1:3306/symfony-tpl
```

Подключаем конфигурацию миграций, фикстур и карты таблиц:

```dotenv
ELOQUENT_CONFIG_FILE=config/eloquent/main.yaml
```

## Объявление в Symfony

Откройте конфиг `config/services.yaml`.

Добавьте конфигурацию менеджера соединений:

```yaml
services:
    ZnCore\Db\Helpers\Manager:
        arguments:
            $mainConfigFile: '%env(ELOQUENT_CONFIG_FILE)%'
```

## Объявление на чистом PHP

```php

require __DIR__ . '/vendor/autoload.php';

\ZnCore\Base\Libs\DotEnv\DotEnv::init();

$eloquentConfigFile = $_ENV['ELOQUENT_CONFIG_FILE'];
$capsule = new Manager(null, $eloquentConfigFile);
```

После чего, можете делать инъекции или использовать класс напрямую.

## Общий конфиг

```yaml
connection:
    map:
        article_category: art_category
        article_post: art_post
        eq_migration: migration
    defaultConnection: pgsqlServer
    connections:
        mysqlServer:
            driver: mysql
            host: localhost
            database: symfony-tpl
            username: root
#            map: карту можно объявлять на каждое соединение отдельно
        pgsqlServer:
            driver: pgsql
            host: localhost
            database: symfony-tpl
            username: postgres
            password: postgres
        sqliteServer:
            driver: sqlite
            database: /var/sqlite/default.sqlite
fixture:
    directory:
        - /src/Fixture
        - /src/Bundle/Article/Domain/Fixture
        - /src/Bundle/User/Fixture
migrate:
    directory:
        - /src/Bundle/Article/Domain/Migration
        - /src/Bundle/User/Migrations
```

Пути:

* `connection.map` - карта алиасов имен таблиц
* `connection.defaultConnection` - имя подключения по умолчанию
* `connection.connections` - подключения к БД
* `fixture.directory` - пути для поиска фикстур
* `migrate.directory` - пути для поиска миграций

Для разбивки таблиц по схемам в *Postgres*, надо разделять имя схемы и таблицы точкой.

Например:

```yaml
connection:
    map:
        article_category: article.category
        article_post: article.post
        eq_migration: system.migration
        cache: system.cache
        messenger_chat: messenger.chat
        messenger_flow: messenger.flow
        messenger_member: messenger.member
        messenger_message: messenger.message
    ...
```

Тут 3 схемы: **article**, **system**, **messenger**.
Хранение данных предметных областей (доменов) логически изолировано.

Получается такой вид БД:

* article
    * category
    * post
* system
    * migration
    * cache
* messenger
    * chat
    * flow
    * member
    * message
