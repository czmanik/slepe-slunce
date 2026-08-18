# Instalace Slepého Slunce

Tento návod počítá s čistým nebo existujícím Ubuntu serverem, nginx, PHP-FPM, MariaDB/MySQL a doménou `slepeslunce.cz`. Aplikace je Laravel 13 + Filament 5 a webroot musí vždy směřovat do adresáře `public`.

## Automatické nasazení

Součástí projektu je `deploy.sh`, který obslouží první instalaci i další aktualizace. Při prvním spuštění vytvoří `.env`, databázi a samostatného databázového uživatele, vygeneruje klíč aplikace, provede migrace, nastaví práva, storage link, scheduler a systemd worker fronty. Při dalších spuštěních bezpečně stáhne zvolenou větev a nasadí změny.

```bash
cd /opt/notm/apps/slepe-slunce
sudo chmod +x deploy.sh
sudo ./deploy.sh main
```

Pro otestování otevřeného PR lze místo `main` zadat jeho větev:

```bash
sudo ./deploy.sh agent/analytics-media-footer-links
```

Skript se při první instalaci zeptá pouze na název databáze a uživatele. Silné databázové heslo vygeneruje a uloží do `.env`; nevypisuje je do logu. Existující `.env` ani existující databázi při dalších nasazeních nepřepisuje. Před migracemi vytvoří databázovou zálohu v `/var/backups/slepe-slunce`, pokud je dostupný `mariadb-dump` nebo `mysqldump`. Log je v `/var/log/slepe-slunce-deploy.log`.

## 1. Požadavky

- PHP 8.3 nebo novější,
- rozšíření PHP: Ctype, cURL, DOM, Fileinfo, Mbstring, OpenSSL, PDO MySQL, Session, Tokenizer a XML,
- Composer 2,
- MariaDB nebo MySQL,
- nginx nebo Apache,
- HTTPS (nutné také pro rychlé vložení GPS polohy z telefonu),
- cron.

Na Ubuntu obvykle stačí balíčky odpovídající vaší verzi PHP:

```bash
sudo apt update
sudo apt install nginx mariadb-server unzip php-fpm php-cli php-mysql php-curl php-mbstring php-xml php-zip php-intl php-gd
```

Composer instalujte podle oficiální dokumentace Composeru. Ověřte:

```bash
php -v
composer --version
```

## 2. Rozbalení projektu

Příklad používá cestu `/opt/notm/apps/slepe-slunce`; můžete ji změnit.

```bash
sudo mkdir -p /opt/notm/apps/slepe-slunce
sudo unzip slepe-slunce-laravel-complete-2026-08-09.zip -d /opt/notm/apps/slepe-slunce
cd /opt/notm/apps/slepe-slunce
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Archiv obsahuje přímo kořen projektu, nikoli další vnořenou složku.

## 3. Databáze

Příklad vytvoření databáze a uživatele:

```sql
CREATE DATABASE slepe_slunce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'slepe_slunce'@'localhost' IDENTIFIED BY 'SEM_VLOZTE_SILNE_HESLO';
GRANT ALL PRIVILEGES ON slepe_slunce.* TO 'slepe_slunce'@'localhost';
FLUSH PRIVILEGES;
```

Do `.env` vyplňte minimálně:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://slepeslunce.cz

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=slepe_slunce
DB_USERNAME=slepe_slunce
DB_PASSWORD=SEM_VLOZTE_SILNE_HESLO
```

Potom spusťte:

```bash
php artisan migrate --seed --force
php artisan storage:link
php artisan app:create-admin
```

Příkaz pro správce se zeptá na e-mail, jméno a heslo. Heslo musí mít alespoň 12 znaků. Administrace bude na `/admin`.

## 4. Oprávnění

Upravte uživatele PHP-FPM/nginx, pokud na serveru nepoužíváte `www-data`:

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 775 {} \;
sudo find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

Zdrojové soubory aplikace není potřeba nastavit jako zapisovatelné.

## 5. nginx

Vzor je v `deploy/nginx.conf.example`. Zkopírujte jej, upravte `server_name`, cestu projektu a PHP-FPM socket:

```bash
sudo cp deploy/nginx.conf.example /etc/nginx/sites-available/slepe-slunce
sudo ln -s /etc/nginx/sites-available/slepe-slunce /etc/nginx/sites-enabled/slepe-slunce
sudo nginx -t
sudo systemctl reload nginx
```

Následně vystavte HTTPS certifikát běžným postupem vašeho serveru. Bez HTTPS nebude prohlížeč na telefonu poskytovat GPS polohu.

## 6. PHP limity pro fotografie

V konfiguraci PHP-FPM nastavte alespoň:

```ini
upload_max_filesize = 16M
post_max_size = 20M
memory_limit = 256M
max_execution_time = 120
```

Po změně restartujte příslušnou službu PHP-FPM. nginx vzor už obsahuje `client_max_body_size 20M`.

## 7. Plánované články

Vytvořte `/etc/cron.d/slepe-slunce`:

```cron
* * * * * www-data cd /opt/notm/apps/slepe-slunce && /usr/bin/php artisan schedule:run >> /var/log/slepe-slunce-scheduler.log 2>&1
```

Soubor cronu musí končit novým řádkem.

## 8. Směrování auta a autobusu

Při uložení úseku typu Auto nebo Autobus server jednorázově požádá směrovací službu o silniční geometrii a výsledek uloží do databáze. Veřejná mapa už směrovací službu nevolá.

Výchozí nastavení v `.env`:

```dotenv
ROUTING_BASE_URL=https://router.project-osrm.org
ROUTING_TIMEOUT=12
```

Veřejná ukázková služba OSRM je vhodná pro malý provoz expedice, ne pro rozsáhlou komerční zátěž. Později ji lze nahradit vlastním OSRM serverem pouhou změnou `ROUTING_BASE_URL`.

Pokud je služba nedostupná, úsek se neztratí: uloží se orientační spojnice. Přepočítání lze provést v editaci úseku nebo hromadně:

```bash
php artisan route:recalculate-geometries
```

## 9. Optimalizace produkce

```bash
php artisan optimize:clear
php artisan optimize
```

Po otevření webu ověřte:

- `/up` vrací odpověď 200,
- `/admin` zobrazí přihlášení,
- `/trasa` se načte i bez JavaScriptu jako časová osa,
- lze nahrát fotografii a zobrazí se z `/storage/...`,
- na HTTPS funguje `/admin/trasa/rychle-pridat` a GPS tlačítko.

## 10. Aktualizace existující instalace

Před aktualizací zazálohujte databázi a adresář `storage/app/public`.

```bash
cd /opt/notm/apps/slepe-slunce
php artisan down
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
sudo chown -R www-data:www-data storage bootstrap/cache
php artisan up
```

Migrace `2026_08_09_000400_create_route_segments_table.php` pouze přidává tabulku přesunů; existující body, články a média nemění.

## 11. Záloha

Zálohujte společně:

- databázi,
- `.env` (bez ukládání do GitHubu),
- `storage/app/public`.

Samotný kód lze kdykoli obnovit z tohoto archivu nebo GitHubu.
