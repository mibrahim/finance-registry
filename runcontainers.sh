docker network create pg_net

echo Stopping
docker stop application
docker stop postgresql
docker stop db

echo deleting
docker rm application
docker rm postgresql
docker rm db

echo running postgres
docker run --name postgresql --net pg_net -d \
  -e 'DB_USER=multidateuser' -e 'DB_PASS=multidatepass' -e 'DB_NAME=multidate' \
  -e 'PG_TRUST_LOCALNET=true' \
  romeoz/docker-postgresql

echo running php/apache
docker run --name application -d -p 8080:80 \
  --net pg_net \
  -v `pwd`:/var/www/app/ \
  romeoz/docker-apache-php
