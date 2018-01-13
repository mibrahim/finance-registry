#!/usr/bin/env bash
docker network create pg_net

echo Stopping
docker stop application
docker stop postgresql
docker stop db
docker stop shell

echo deleting
docker rm application
docker rm postgresql
docker rm db
docker rm shell

echo running postgres
docker run --name postgresql --net pg_net -p 65432:5432 -d \
  -e 'DB_USER=user' -e 'DB_PASS=pass' -e 'DB_NAME=multidate' \
  -e 'PG_TRUST_LOCALNET=true' \
  -v `pwd`:/var/project \
  -v `pwd`/data:/var/lib/postgresql \
  romeoz/docker-postgresql

until docker exec -i -t postgresql sudo -u postgres ls -al /var/run/postgresql/ | grep 5432; do sleep 1 | echo "waiting for posgres to start..."; done

docker exec -i -t postgresql sudo -u postgres psql -f /var/project/setupdb.sql multidate

chmod -R 777 ./data/

echo running php/apache
docker run --name application -d -p 8080:80 \
  --net pg_net \
  -v `pwd`:/var/www/app/ \
  romeoz/docker-apache-php
