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

${SUDO} docker start mdpsql
${SUDO} docker start mdcontainer

ID=`./containerid.sh`
${SUDO} docker exec ${ID} /etc/init.d/apache2 start
${SUDO} docker exec ${ID} /etc/init.d/postgresql start
${SUDO} docker exec ${ID} /etc/init.d/ssh start