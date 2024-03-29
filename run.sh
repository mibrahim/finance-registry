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
${SUDO} docker run -d -p 127.0.0.1:8123:80 --name multidate-app -v "$PWD/web/":/var/www/html php:8.0-apache
