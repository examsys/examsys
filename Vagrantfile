# Vagrant is a tool for building and managing virtual machine environments in a
# single workflow. With an easy-to-use workflow and focus on automation, Vagrant
# lowers development environment setup time, increases production parity, and
# makes the "works on my machine" excuse a relic of the past.
#
# For more details visit `https://www.vagrantup.com/` or just type `vagrant up`
# to start your local Rogo environment.
#
Vagrant.configure("2") do |config|
  config.vm.box = "bento/ubuntu-14.04"
  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.network "forwarded_port", guest: 443, host: 44433
  config.vm.synced_folder ".", "/var/www", owner: "www-data", group: "www-data"
  config.vm.provision "shell", inline: <<-SHELL
    # update packages
    apt-get update

    # install goodies
    apt-get install -y curl git zip

    # install NodeJS and npm
    curl -sL https://deb.nodesource.com/setup_7.x | sudo -E bash -
    apt-get install -y nodejs

    # install MySQL (root / Passw0rd)
    debconf-set-selections <<< 'mysql-server mysql-server/root_password password Passw0rd'
    debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password Passw0rd'
    apt-get install -y mysql-server

    # install PHP 5 with required extensions
    apt-get install -y php5 php5-gd php5-curl php5-xmlrpc php5-mysql php5-mcrypt
    php5enmod mcrypt

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

    # restart Apache
    service apache2 restart

    # install Composer
    if [ -e /usr/local/bin/composer ]; then
        /usr/local/bin/composer self-update
    else
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
        cp /usr/local/bin/composer /var/www/composer.phar
    fi

    # reset home directory of vagrant user
    if ! grep -q "cd /var/www" /home/vagrant/.profile; then
        echo "cd /var/www" >> /home/vagrant/.profile
    fi

    # run composer install
    cd /var/www
    composer install

    # done!
    echo "[ROGO] https://localhost:44433/"
  SHELL
end
