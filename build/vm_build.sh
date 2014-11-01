#!/usr/bin/env bash

#need this to get 'libapache2-mod-fastcgi'
sudo sed -i "/^# deb.*multiverse/ s/^# //" /etc/apt/sources.list
sudo apt-get update

sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password password root'
sudo debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password root'

sudo apt-get install -y curl apache2 libapache2-mod-fastcgi php5 php5-fpm php5-cli php5-curl php5-gd php5-mcrypt php5-mysql mysql-server

sudo sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php5/fpm/php.ini
sudo sed -i "s/display_errors = .*/display_errors = On/" /etc/php5/fpm/php.ini
sudo sed -i "s/disable_functions = .*/disable_functions = /" /etc/php5/cli/php.ini
sudo sed -i "s/memory_limit = .*/memory_limit = 1024M/" /etc/php5/fpm/php.ini
sudo sed -i "s#date\.timezone.*#date\.timezone = \"Europe\/London\"#" /etc/php5/fpm/php.ini

sudo a2enmod rewrite actions fastcgi alias

sudo bash -c "cat >> /etc/apache2/conf.d/servername.conf <<EOF
ServerName localhost
EOF"

WEBROOT="/vagrant/"
sudo echo "<VirtualHost *:80>
  DocumentRoot $WEBROOT

  <Directory $WEBROOT>
    Options FollowSymLinks MultiViews ExecCGI
    AllowOverride All
    Order deny,allow
    Allow from all
  </Directory>

  <IfModule mod_fastcgi.c>
    AddHandler php5-fcgi .php
    Action php5-fcgi /php5-fcgi
    Alias /php5-fcgi /usr/lib/cgi-bin/php5-fcgi
    FastCgiExternalServer /usr/lib/cgi-bin/php5-fcgi -host 127.0.0.1:9000 -pass-header Authorization
  </IfModule>

</VirtualHost>" | sudo tee /etc/apache2/sites-available/default > /dev/null

sudo service apache2 restart
sudo service php5-fpm restart

mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS magehero;"
mysql -uroot -proot magehero < /vagrant/sql/mysql-1.0.0.sql
mysql -uroot -proot magehero < /vagrant/sql/mysql-1.0.1.sql
mysql -uroot -proot magehero < /vagrant/sql/mysql-1.0.2.sql

sudo bash -c "cat >> /etc/hosts <<EOF
127.0.0.1 magehero.local
EOF"
