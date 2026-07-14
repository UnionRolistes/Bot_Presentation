# Formulaire de présentation (presentation.unionrolistes.fr).
#
# Pas de CGI ici — même logique que web-planning (Bot_Planning_python) :
# le CGI Python postait directement sur Discord via discord.py pour un
# simple webhook (un POST JSON), remplacé par un appel PHP direct. Plus
# d'interpréteur Python, plus de mod_cgi dans ce conteneur.
FROM php:8.4-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY www/ /var/www/html/

# php/templates/ n'a pas besoin d'être servi en HTTP (lu directement sur
# disque par create_presentation.php).
RUN { \
    echo '<Directory "/var/www/html">'; \
    echo '    Options -Indexes +FollowSymLinks'; \
    echo '    AllowOverride All'; \
    echo '    Require all granted'; \
    echo '</Directory>'; \
    echo '<Directory "/var/www/html/php/templates">'; \
    echo '    Require all denied'; \
    echo '</Directory>'; \
    } > /etc/apache2/conf-enabled/presentation.conf

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
