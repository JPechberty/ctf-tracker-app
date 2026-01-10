# 11. Infrastructure and Deployment

## 11.1 Server Requirements

- Ubuntu 24.04 LTS (ou équivalent)
- PHP 8.4-FPM
- Nginx
- Composer

## 11.2 Deployment Script

```bash
#!/bin/bash
# deploy.sh

set -e
APP_DIR="/var/www/ctf-tracker"

echo "Backup DB..."
cp $APP_DIR/var/data/ctf.db /var/backups/ctf_$(date +%Y%m%d_%H%M%S).db 2>/dev/null || true

echo "Pull code..."
cd $APP_DIR && git pull origin main

echo "Install deps..."
composer install --no-dev --optimize-autoloader

echo "Migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

echo "Clear cache..."
php bin/console cache:clear --env=prod

echo "Done!"
```

## 11.3 Nginx Configuration

```nginx
server {
    listen 80;
    server_name ctf.example.com;
    root /var/www/ctf-tracker/public;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ ^/index\.php(/|$) {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        internal;
    }
}
```

---
