#!/bin/bash


php bin/console doctrine:database:drop --force --env=test
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migration:migrate --env=test --no-interaction
php bin/console doctrine:fixtures:load --env=test --no-interaction

php bin/phpunit "$@"
