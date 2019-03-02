#!/usr/bin/env bash
SUDO="sudo"
uNameOut="$(uname -s)"
case "${uNameOut}" in
    CYGWIN*)    
        SUDO=""
        ;;
    MINGW*)     
        SUDO=""
        ;;
esac

./stop.sh
${SUDO} docker rm --force multidate-app
${SUDO} docker run -d -p 80:80 --name multidate-app -v "$PWD/web/src/":/var/www/html php:7.2-apache
