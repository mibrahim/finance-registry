#!/bin/sh

# Install openssh server, less, sudo, tmux, rsync
LIBS="locales dialog vim screen bash-completion openssh-server less sudo tmux rsync sendmail"

# Install postgresql
LIBS="$LIBS postgresql-client"

# Install apache and php
LIBS="$LIBS apache2 libapache2-mod-php7.0 apache2-utils php php7.0 php7.0-xml php7.0-pgsql php7.0-cli php-pear"

apt-get update && apt-get install -y $LIBS && apt-get clean && rm -rf /var/lib/apt/lists/*
