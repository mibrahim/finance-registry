#!/bin/sh

# Install openssh server, less, sudo, tmux, rsync
LIBS="locales dialog vim screen bash-completion openssh-server less sudo sendmail"

# Install postgresql
LIBS="$LIBS postgresql-client"

# Install apache and php
LIBS="$LIBS apache2 libapache2-mod-php7.2 apache2-utils php php7.2 php7.2-xml php7.2-pgsql php7.2-cli php-pear"

export DEBIAN_FRONTEND=noninteractive

apt-get update
apt-get install -y $LIBS
apt-get clean
rm -rf /var/lib/apt/lists/*
