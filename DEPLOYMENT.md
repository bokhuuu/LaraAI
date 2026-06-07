# LaraAI - Deployment Guide

This guide covers deploying LaraAI to a production VPS using Laravel Forge or manual setup.

---

## Requirements

- Ubuntu 22.04 VPS (2GB+ RAM minimum, 4GB+ recommended for Ollama)
- PHP 8.4
- MySQL 8.0
- Redis 7
- Nginx
- Composer
- Node.js 20 (required for MCP memory server via npx)
- Ghostscript + Imagick (required for PDF extraction)
- OpenRouter API key
- Domain name pointed at your server IP

---

## Option A - Laravel Forge (recommended)

Forge automates server provisioning, Nginx config, SSL and deployments.

### 1. Provision the server

- Create a new server in Forge (Ubuntu 22.04)
- Select PHP 8.4
- Forge installs Nginx, PHP-FPM, MySQL, Redis automatically

### 2. Install extra dependencies

SSH into the server and run:

```bash
sudo apt-get update
sudo apt-get install -y ghostscript libmagickwand-dev
sudo pecl install imagick
sudo docker-php-ext-enable imagick
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo bash -
sudo apt-get install -y nodejs
```

### 3. Create the site in Forge

- Add a new site with your domain
- Set the web root to `/current/public` (if using zero-downtime deploys)
- Enable SSL via Let's Encrypt

### 4. Set environment variables

In Forge → Site → Environment, set all values from `.env.example`:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=laraai
DB_USERNAME=forge
DB_PASSWORD=your-db-password

REDIS_HOST=127.0.0.1
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

AI_DEFAULT_PROVIDER=openrouter
AI_PRODUCTION_PROVIDER=openrouter
OPENROUTER_API_KEY=your-key-here

SENTRY_LARAVEL_DSN=your-sentry-dsn

WEBHOOK_SECRET=your-webhook-secret
HORIZON_ALERT_EMAIL=your@email.com
SLACK_ALERTS_WEBHOOK_URL=your-slack-webhook
```

### 5. Deploy script in Forge

```bash
cd /home/forge/yourdomain.com
git pull origin main
composer install --no-interaction --optimize-autoloader --no-dev
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan horizon:terminate
```

### 6. Start Horizon

In Forge → Daemons, add:

```
Command: php /home/forge/yourdomain.com/artisan horizon
User: forge
```

Forge keeps it running and restarts it automatically.

---

## Option B - Manual VPS Setup

### 1. Install dependencies

```bash
sudo apt-get update
sudo apt-get install -y nginx php8.4-fpm php8.4-mysql php8.4-redis \
    php8.4-pcntl php8.4-imagick mysql-server redis-server \
    ghostscript libmagickwand-dev composer git unzip

curl -fsSL https://deb.nodesource.com/setup_20.x | sudo bash -
sudo apt-get install -y nodejs
```

### 2. Clone the project

```bash
cd /var/www
git clone git@github.com:bokhuuu/LaraAI.git
cd LaraAI
composer install --no-interaction --optimize-autoloader --no-dev
cp .env.example .env
php artisan key:generate
```

### 3. Configure Nginx

Create `/etc/nginx/sites-available/laraai`:

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/LaraAI/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Enable and reload:

```bash
sudo ln -s /etc/nginx/sites-available/laraai /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 4. Set permissions

```bash
sudo chown -R www-data:www-data /var/www/LaraAI/storage
sudo chown -R www-data:www-data /var/www/LaraAI/bootstrap/cache
sudo chmod -R 775 /var/www/LaraAI/storage
```

### 5. Set up database

```bash
sudo mysql -u root -p
CREATE DATABASE laraai;
CREATE USER 'laraai'@'localhost' IDENTIFIED BY 'your-password';
GRANT ALL PRIVILEGES ON laraai.* TO 'laraai'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Then run migrations:

```bash
php artisan migrate --force
```

### 6. Start Horizon as a system service

Create `/etc/supervisor/conf.d/laraai-horizon.conf`:

```ini
[program:laraai-horizon]
command=php /var/www/LaraAI/artisan horizon
user=www-data
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/www/LaraAI/storage/logs/horizon.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laraai-horizon
```

### 7. Enable SSL

```bash
sudo apt-get install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com
```

---

## Production vs Development differences

| Setting               | Development        | Production             |
| --------------------- | ------------------ | ---------------------- |
| `APP_DEBUG`           | `true`             | `false`                |
| `APP_ENV`             | `local`            | `production`           |
| `AI_DEFAULT_PROVIDER` | `ollama`           | `openrouter`           |
| Ollama container      | running            | not needed             |
| Cache                 | `array` or `redis` | `redis`                |
| Queue                 | `sync` or `redis`  | `redis`                |
| Telescope             | enabled            | disabled or restricted |

---

## Production docker-compose (optional)

If deploying with Docker on a VPS, create `docker-compose.prod.yml`:

```yaml
services:
    app:
        build:
            context: .
            dockerfile: Dockerfile
        restart: unless-stopped
        environment:
            APP_ENV: production
            APP_DEBUG: false
        volumes:
            - storage-data:/var/www/storage
        networks:
            - laraai

    mysql:
        image: mysql:8.0
        restart: unless-stopped
        environment:
            MYSQL_DATABASE: ${DB_DATABASE}
            MYSQL_ROOT_PASSWORD: ${DB_PASSWORD}
        volumes:
            - mysql-data:/var/lib/mysql
        networks:
            - laraai

    redis:
        image: redis:7-alpine
        restart: unless-stopped
        volumes:
            - redis-data:/data
        networks:
            - laraai

networks:
    laraai:
        driver: bridge

volumes:
    mysql-data:
    redis-data:
    storage-data:
```

Key differences from development:

- No Ollama container - use OpenRouter in production
- No port exposure for MySQL/Redis - internal only
- No volume mount of source code - code baked into image
- Storage volume persists uploaded webhook files

Deploy with:

```bash
docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

---

## Post-deployment checklist

- [ ] `APP_DEBUG=false` confirmed
- [ ] `OPENROUTER_API_KEY` set
- [ ] `WEBHOOK_SECRET` set
- [ ] Horizon running and visible at `/horizon`
- [ ] Health check returning 200: `GET /api/ai/health`
- [ ] Sentry DSN configured
- [ ] Slack webhook URL set for cost alerts
- [ ] SSL certificate active
- [ ] Storage permissions correct
- [ ] Config cached: `php artisan config:cache`

---

## pgvector (for 1000+ documents)

The default RAG implementation loads all documents into PHP memory for vector comparison. For large datasets switch to PostgreSQL with pgvector:

1. Switch `DB_CONNECTION` to `pgsql`
2. Install pgvector extension: `CREATE EXTENSION vector;`
3. Change `embedding` column type to `vector(768)` in the documents migration
4. Replace `EmbeddingService::search()` with a DB-level nearest-neighbor query:

```sql
SELECT * FROM documents
ORDER BY embedding <=> '[your_query_vector]'
LIMIT 5;
```

This runs the comparison inside PostgreSQL - no PHP memory load regardless of document count.

---

_For questions about the template see README.md. For architecture overview see the Mermaid diagram in README.md._
