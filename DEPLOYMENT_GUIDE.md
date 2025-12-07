# Moodiary Deployment Guide

## Local Development Setup

### Prerequisites
- PHP 8.2+
- MySQL 8.0+
- Node.js 18+
- Composer
- Git

### Step 1: Clone Repository
```bash
git clone https://github.com/yourusername/moodiary.git
cd moodiary
```

### Step 2: Install Dependencies
```bash
composer install
npm install
```

### Step 3: Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```
APP_NAME=Moodiary
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=moodiary
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=cookie

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Step 4: Database Setup
```bash
php artisan migrate
php artisan db:seed
```

### Step 5: Build Assets
```bash
npm run dev
# or for production
npm run build
```

### Step 6: Start Development Server
```bash
php artisan serve
```

Visit: `http://localhost:8000`

---

## Shared Hosting / cPanel Deployment

### Prerequisites
- cPanel access
- PHP 8.2+ installed
- MySQL database
- SSH access (recommended)

### Step 1: Upload Files

**Via FTP:**
1. Connect to FTP server
2. Navigate to `public_html` directory
3. Upload all project files
4. Ensure proper directory structure

**Via Git (if available):**
```bash
ssh user@domain.com
cd public_html
git clone https://github.com/yourusername/moodiary.git .
```

### Step 2: Create Database

**Via cPanel:**
1. Go to MySQL Databases
2. Create new database: `moodiary`
3. Create user: `moodiary_user`
4. Assign user to database with all privileges
5. Note the credentials

### Step 3: Configure Environment
```bash
cp .env.example .env
```

Edit `.env` with database credentials:
```
DB_HOST=localhost
DB_DATABASE=moodiary
DB_USERNAME=moodiary_user
DB_PASSWORD=your_password
```

### Step 4: Install Composer Dependencies
```bash
php composer.phar install --no-dev --optimize-autoloader
```

Or if Composer is installed globally:
```bash
composer install --no-dev --optimize-autoloader
```

### Step 5: Generate Application Key
```bash
php artisan key:generate
```

### Step 6: Run Migrations
```bash
php artisan migrate --force
```

### Step 7: Set Permissions
```bash
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage bootstrap/cache public/storage
```

### Step 8: Build Frontend Assets
```bash
npm install
npm run build
```

### Step 9: Configure Cron Job

**Via cPanel:**
1. Go to Cron Jobs
2. Add new cron job
3. Set frequency: Every minute
4. Command:
```bash
cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1
```

### Step 10: Configure Queue Worker

**Option 1: Using Supervisor (VPS)**
```bash
sudo apt-get install supervisor
```

Create `/etc/supervisor/conf.d/moodiary.conf`:
```ini
[program:moodiary-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/moodiary/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/moodiary/storage/logs/worker.log
```

Restart supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start moodiary-worker:*
```

**Option 2: Using Database Queue (Shared Hosting)**
```
QUEUE_CONNECTION=database
```

### Step 11: SSL Certificate

**Let's Encrypt (cPanel):**
1. Go to AutoSSL
2. Enable automatic SSL
3. Wait for certificate generation

**Or manually:**
```bash
certbot certonly --webroot -w /home/username/public_html -d domain.com
```

### Step 12: Configure Web Server

**Apache (.htaccess already included):**
- Ensure `mod_rewrite` is enabled
- Ensure `.htaccess` is in public directory

**Nginx (if using Nginx):**
```nginx
server {
    listen 80;
    server_name domain.com;
    root /home/username/public_html/public;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## VPS / Dedicated Server Deployment

### Prerequisites
- Ubuntu 20.04+ or similar
- Root or sudo access
- Domain name

### Step 1: System Setup
```bash
sudo apt update
sudo apt upgrade -y
sudo apt install -y curl wget git zip unzip
```

### Step 2: Install PHP 8.2
```bash
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-redis php8.2-gd php8.2-curl php8.2-mbstring php8.2-xml php8.2-zip
```

### Step 3: Install MySQL
```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

### Step 4: Install Redis
```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

### Step 5: Install Nginx
```bash
sudo apt install -y nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

### Step 6: Install Composer
```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Step 7: Install Node.js
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

### Step 8: Clone Repository
```bash
cd /var/www
sudo git clone https://github.com/yourusername/moodiary.git
sudo chown -R www-data:www-data moodiary
cd moodiary
```

### Step 9: Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### Step 10: Configure Environment
```bash
sudo cp .env.example .env
sudo nano .env
```

Set:
```
APP_ENV=production
APP_DEBUG=false
DB_HOST=localhost
DB_DATABASE=moodiary
DB_USERNAME=moodiary
DB_PASSWORD=strong_password
```

### Step 11: Generate Key & Run Migrations
```bash
php artisan key:generate
php artisan migrate --force
```

### Step 12: Set Permissions
```bash
sudo chown -R www-data:www-data storage bootstrap/cache public/storage
sudo chmod -R 755 storage bootstrap/cache
sudo chmod -R 777 storage bootstrap/cache
```

### Step 13: Configure Nginx
```bash
sudo nano /etc/nginx/sites-available/moodiary
```

Add:
```nginx
server {
    listen 80;
    server_name domain.com www.domain.com;
    root /var/www/moodiary/public;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/moodiary /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Step 14: SSL Certificate
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d domain.com -d www.domain.com
```

### Step 15: Configure Cron Job
```bash
sudo crontab -e
```

Add:
```
* * * * * cd /var/www/moodiary && php artisan schedule:run >> /dev/null 2>&1
```

### Step 16: Setup Queue Worker with Supervisor
```bash
sudo apt install -y supervisor
sudo nano /etc/supervisor/conf.d/moodiary.conf
```

Add:
```ini
[program:moodiary-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/moodiary/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/moodiary/storage/logs/worker.log
user=www-data
```

Start:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start moodiary-worker:*
```

---

## Post-Deployment Checklist

- [ ] Database migrations completed
- [ ] Environment variables configured
- [ ] Storage directory writable
- [ ] Cache cleared: `php artisan cache:clear`
- [ ] Config cached: `php artisan config:cache`
- [ ] Routes cached: `php artisan route:cache`
- [ ] SSL certificate installed
- [ ] Cron job configured
- [ ] Queue worker running
- [ ] Redis connection verified
- [ ] Email configuration tested
- [ ] File uploads working
- [ ] Admin user created
- [ ] Backup system configured
- [ ] Monitoring setup

---

## Troubleshooting

### 500 Error
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Permission Denied
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 755 storage bootstrap/cache
```

### Database Connection Error
- Verify credentials in `.env`
- Check MySQL is running: `sudo systemctl status mysql`
- Test connection: `php artisan tinker` → `DB::connection()->getPdo()`

### Queue Not Processing
- Check Redis: `redis-cli ping` (should return PONG)
- Check supervisor: `sudo supervisorctl status`
- View logs: `tail -f storage/logs/worker.log`

### High Memory Usage
- Reduce queue workers
- Optimize database queries
- Enable query caching
- Use pagination

---

## Backup Strategy

### Daily Database Backup
```bash
0 2 * * * mysqldump -u root -p'password' moodiary > /backups/moodiary_$(date +\%Y\%m\%d).sql
```

### Weekly Full Backup
```bash
0 3 * * 0 tar -czf /backups/moodiary_$(date +\%Y\%m\%d).tar.gz /var/www/moodiary
```

### S3 Backup
```bash
php artisan backup:run --only-db
```

---

## Monitoring

### Log Monitoring
```bash
tail -f storage/logs/laravel.log
```

### Performance Monitoring
- Setup New Relic or DataDog
- Monitor CPU, memory, disk
- Track database query times
- Monitor queue depth

### Uptime Monitoring
- Setup Uptime Robot
- Configure alerts
- Monitor response times
