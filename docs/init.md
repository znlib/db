# Развертывание базы данных

Выполнить миграции:

    cd vendor/znlib/migration/bin
    php console db:migrate:up --withConfirm=0

Выполнить иморт демо-данных в БД для разработки:

    cd vendor/znlib/fixture/bin
    php console db:fixture:import --withConfirm=0

Команды выполнятся без подтверждений.
