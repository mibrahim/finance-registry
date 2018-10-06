#!/usr/bin/env bash
echo ========
echo Will run using sudo. You will be prompted to enter your password
echo ========

SUDO="sudo"
unameOut="$(uname -s)"
case "${unameOut}" in
    CYGWIN*)    SUDO="";;
    MINGW*)     SUDO="";;
esac

./stop.sh

${SUDO} docker rmi --force mdimage
${SUDO} docker build -t="mdimage" .
