# Vagrant is a tool for building and managing virtual machine environments in a
# single workflow. With an easy-to-use workflow and focus on automation, Vagrant
# lowers development environment setup time, increases production parity, and
# makes the "works on my machine" excuse a relic of the past.
#
# For more details visit `https://www.vagrantup.com/` or just type `vagrant up`
# to start your local Rogo environment.
#
Vagrant.configure("2") do |config|
  config.vm.box = "bento/ubuntu-18.04"
  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.network "forwarded_port", guest: 443, host: 44433
  config.vm.synced_folder ".", "/var/www", owner: "www-data", group: "www-data"
  config.vm.provision "shell", inline: <<-SHELL
    # update packages
    apt-get update

    # install goodies
    apt-get install -y npm r-cran-rserve memcached wbritish

    # install MySQL (root / Passw0rd)
    debconf-set-selections <<< 'mysql-server mysql-server/root_password password Passw0rd'
    debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password Passw0rd'
    apt-get install -y mysql-server

    # install PHP 7.2 with required extensions
    apt-get install -y php-gd php-curl php-xml php-xmlrpc php-mysql php-intl php-ldap php-mbstring php-zip php-memcached

    # install Apache with PHP
    apt-get install -y apache2 libapache2-mod-php

    # create virtual hosts
    echo "<VirtualHost *:80>
            DocumentRoot /var/www
            AllowEncodedSlashes On
            <Directory /var/www>
                    Options +Indexes +FollowSymLinks
                    DirectoryIndex index.php index.html
                    Order allow,deny
                    Allow from all
                    AllowOverride All
            </Directory>
            ErrorLog ${APACHE_LOG_DIR}/error.log
            CustomLog ${APACHE_LOG_DIR}/access.log combined
    </VirtualHost>
    <VirtualHost *:443>
            DocumentRoot /var/www
            AllowEncodedSlashes On
            <Directory /var/www>
                    Options +Indexes +FollowSymLinks
                    DirectoryIndex index.php index.html
                    Order allow,deny
                    Allow from all
                    AllowOverride All
            </Directory>
            ErrorLog ${APACHE_LOG_DIR}/error.log
            CustomLog ${APACHE_LOG_DIR}/access.log combined
            SSLEngine on
            SSLCertificateFile /etc/ssl/certs/ssl-cert-snakeoil.pem
            SSLCertificateKeyFile /etc/ssl/private/ssl-cert-snakeoil.key
    </VirtualHost>
" > /etc/apache2/sites-available/rogo.conf

    # enable apache mods
    a2enmod rewrite
    a2enmod ssl

    # set Apache virtual hosts
    a2dissite 000-default
    a2ensite rogo
    rm -rf /var/www/html

    # rogo php settings
    echo "
max_execution_time = 120
memory_limit = 512M
post_max_size = 20M
upload_max_filesize = 20M
default_charset = "utf-8"
mbstring.internal_encoding = UTF-8
max_input_vars = 3000
session.save_handler = memcached
session.save_path = "localhost:11211"
" > /etc/php/7.2/apache2/conf.d/20-user.ini

    # restart Apache
    service apache2 restart

    # reset home directory of vagrant user
    if ! grep -q "cd /var/www" /home/vagrant/.profile; then
        echo "cd /var/www" >> /home/vagrant/.profile
    fi

    # create data dir
    cd /
    mkdir rogodata

    # remove config file
    if [ -e /var/www/config/config.inc.php ]; then
        cd /var/www/config
        rm config.inc.php
    fi

    if [ -e /var/www/config/settings.xml ]; then
        # install rogo via setting file
        cd /var/www
        php cli/init.php -uroot -pPassw0rd -s127.0.0.1 -t3306 -nrogo
    else
        # manual install - just get composer files
        cd /var/www
        wget https://getcomposer.org/composer.phar
        php composer.phar install
    fi

    # set data dir perms
    cd /
    chown -R www-data:www-data rogodata

    # start up Rrserve
    R CMD Rserve --no-save

    # done!
    echo "[ROGO] https://localhost:44433/"
  SHELL
end
