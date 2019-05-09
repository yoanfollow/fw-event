#!/bin/sh

set -e

# Copy parameters.yml.dist if it doesn't exists yet
if [ ! -f /var/www/project/.env ]
  then
  cp /var/www/project/.env.dist /var/www/project/.env
fi

if [ ! -f /var/www/project/var/log/dev.log ]
    then
    touch /var/www/project/var/log/dev.log
fi

cd /var/www/project &&  composer install

if [ ! -d config/jwt ]; then
    mkdir -p config/jwt
fi

if [ ! -f config/jwt/private.pem ] || [ ! -f config/jwt/public.pem ]; then
    jwt_passhrase=$(grep ''^JWT_PASSPHRASE='' .env | cut -f 2 -d ''='')
    echo "$jwt_passhrase" | openssl genpkey -out config/jwt/private.pem -pass stdin -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
    echo "$jwt_passhrase" | openssl pkey -in config/jwt/private.pem -passin stdin -out config/jwt/public.pem -pubout
fi

php /var/www/project/bin/console doctrine:migrations:migrate
php /var/www/project/bin/console doctrine:fixtures:load

echo " ====== READY ====== "
echo "Allons-y Alonso!"

