#!/bin/sh

export LANGUAGE="en_US.UTF-8"
echo 'LANGUAGE="en_US.UTF-8"' >> /etc/default/locale
echo 'LC_ALL="en_US.UTF-8"' >> /etc/default/locale

locale-gen en_US.UTF-8

cp /conf/apachesites/site-md.conf /etc/apache2/sites-available/000-default.conf
cp /conf/apachesites/apache2.conf /etc/apache2/apache2.conf
cp /conf/phpconf/php.ini /etc/php/7.2/apache2/php.ini

# Enable mod rewrite
ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/

echo Create user md
adduser --gecos "" --disabled-password md
echo Add md to group www-data
adduser --quiet md www-data

echo 'md:md' | chpasswd

chown -R md:www-data /home/md/
cp /conf/bashrc /home/md/.bashrc
cp /conf/screenrc /home/md/.screenrc

ln -s /home/web /home/md/web
chown -R md:www-data /home/md

echo 'md  ALL=(ALL:ALL) ALL' > /etc/sudoers.d/md

echo "\n127.0.0.1       localdocker localhost localhost.localdomain" >> /etc/hosts
