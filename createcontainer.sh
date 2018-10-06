#!/usr/bin/env bash
SUDO="sudo"
unameOut="$(uname -s)"
case "${unameOut}" in
    CYGWIN*)    
        SUDO=""
        ;;
    MINGW*)     
        SUDO=""
        ;;
esac

echo Stopping mdcontainer if existed
./stop.sh
echo Removing mdcontainer
${SUDO} docker rm --force mdcontainer
${SUDO} docker rm --force mdpsql

echo Create the network and the database images

${SUDO} docker network create mdpg_net

if [ ! -d ./pgdata ]; then
    mkdir ./pgdata
    ${SUDO} chcon -Rt svirt_sandbox_file_t ./pgdata
fi

${SUDO} docker run --name='mdpsql' -d \
    --net mdpg_net \
    -e 'DB_USER=md' -e 'DB_PASS=psql1234' \
    -e 'DB_NAME=mddb' \
    -e 'PG_TRUST_LOCALNET=true' \
    -v `readlink -f ./pgdata`:/var/lib/postgresql \
    -v `readlink -f ./conf`:/conf \
    romeoz/docker-postgresql

echo Run docker image with new container hhcontainer
${SUDO} docker run --hostname=localdocker -it -p 4022:22 -p 4080:80 \
    --net mdpg_net \
    -v `readlink -f ./web/`:/home/web/ \
    -v `readlink -f ./conf`:/conf \
    --name mdcontainer -d mdimage /bin/bash

echo Done with docker run

sleep 5

ID=`./containerid.sh`
echo ID = ${ID}
${SUDO} docker exec ${ID} ./conf/config.sh
sleep 4
${SUDO} docker exec mdpsql sudo -u postgres /conf/postgres.sh

echo Done... stopping
./stop.sh
