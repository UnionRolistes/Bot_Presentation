# Formulaire de présentation (presentation.unionrolistes.fr).
#
# php-fpm + nginx sur Alpine plutôt que php:8.4-apache (Debian) : le site
# n'utilise ni mod_rewrite (aucune RewriteRule/RewriteCond dans le repo)
# ni aucune fonctionnalité Apache au-delà d'un .htaccess absent ici (seul
# Bot_Planning_python/web/ en a un). curl est déjà compilé statiquement
# dans php:8.4-fpm-alpine, comme dans la variante -apache.
FROM php:8.4-fpm-alpine

RUN apk add --no-cache nginx

COPY nginx.conf /etc/nginx/nginx.conf

COPY www/ /var/www/html/

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]

# php-fpm en arriere-plan (-D), nginx au premier plan comme PID 1.
CMD ["sh", "-c", "php-fpm -D && exec nginx -g 'daemon off;'"]
