FROM php:8.2-apache
# FROM php:7.4-apache

# Instala extensão necessária para MySQL
RUN docker-php-ext-install mysqli

# Ativa mod_rewrite (seguro ativar)
RUN a2enmod rewrite

# Define diretório de trabalho
WORKDIR /var/www/html
