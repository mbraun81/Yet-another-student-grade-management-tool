# syntax=docker/dockerfile:1

# ---- Composer binary ----
FROM composer:2 AS composer

# ---- App image ----
FROM php:8.4-fpm-bookworm

# System packages
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        nginx \
        supervisor \
        libpq-dev \
        libldap2-dev \
        libicu-dev \
        libzip-dev \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        ldap \
        intl \
        opcache \
        zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Nginx config
COPY docker/nginx/default.conf /etc/nginx/sites-available/default

# Supervisord config
RUN mkdir -p /var/log/supervisor
COPY <<'EOF' /etc/supervisor/conf.d/app.conf
[supervisord]
nodaemon=true
logfile=/var/log/supervisor/supervisord.log

[program:php-fpm]
command=php-fpm --nodaemonize
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
EOF

WORKDIR /var/www/html

# Default PHP-FPM pool: listen on 127.0.0.1:9000 (already default)

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
