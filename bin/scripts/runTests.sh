#!/usr/bin/env sh

docker-compose down
docker-compose up -d
docker-compose exec -T php sh -c 'bin/console assets:install --no-interaction'
docker-compose exec php sh -c 'bin/console doctrine:database:drop -f'
docker-compose exec php sh -c 'bin/console doctrine:database:create'
docker-compose exec php sh -c 'bin/console doctrine:migrations:migrate --allow-no-migration --no-interaction'
docker-compose exec php sh -c 'bin/console doctrine:fixtures:load --no-interaction'
docker-compose exec php sh -c 'bin/console cache:clear'
docker-compose exec php sh -c 'bin/phpunit'
