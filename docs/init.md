# Развертывание базы данных

Выполнить миграции:

    cd vendor/znlib/migration/bin
    php console db:migrate:up

Выполнить иморт демо-данных в БД для разработки:

    cd vendor/znlib/fixture/bin
    php console db:fixture:import

Для выполнения команд без подтверждений, используйте опцию `--withConfirm=0`.
