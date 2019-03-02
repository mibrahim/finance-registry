#!/usr/bin/env bash
echo Stopping `./containerid.sh`
SUDO="sudo"
unameOut="$(uname -s)"
case "${unameOut}" in
    CYGWIN*)    SUDO="";;
    MINGW*)     SUDO="";;
esac
${SUDO} docker stop `./containerid.sh`
