# Slepé Slunce

Přístupný expediční deník postavený na Laravelu 13 a Filamentu 5. Veřejná část je v Blade a vlastním CSS; interaktivní mapa používá Leaflet a OpenStreetMap.

## Co obsahuje první verze

- veřejnou úvodní stránku, deník, detail příspěvku, mapu a časovou osu,
- administraci na `/admin`,
- koncepty, plánování, publikování a archivaci,
- oddělené uživatelské účty a veřejné autorství,
- role správce, editor a autor,
- hlavní fotografii a řazenou galerii s povinnými alternativními texty,
- řazená YouTube videa v režimu `youtube-nocookie.com`, popis a přepis,
- přihlášený náhled článku před publikací,
- sitemapu, SEO pole a sdílecí metadata,
- bezpečné čištění HTML z textového editoru,
- samostatné úseky cesty s autem, autobusem, letem, vlakem, pěší cestou, kolem a lodí,
- plánované i skutečné odjezdy a příjezdy, vzdálenost a trvání,
- uloženou silniční geometrii pro auto a autobus a oblouk pro lety,
- fotografie, video a články také přímo u přesunů,
- testy veřejného publikačního workflow a výpočtu geometrie.

## Trasa expedice

V administraci otevřete **Expedice → Trasa** pro místa a **Expedice → Přesuny** pro cestu mezi nimi. Každý přesun zná dopravu, stav, plánované i skutečné časy, vzdálenost, trvání, média a propojený článek. Auto a autobus lze vést po silnici, letadlo se vykreslí jako oblouk.

Tlačítko **Rychle přidat z telefonu** otevře zjednodušený formulář. Tlačítko pro aktuální polohu funguje na HTTPS (nebo na localhostu) a po souhlasu uživatele vloží GPS souřadnice telefonu. Systém automaticky převede předchozí bod `Jsme tady` na `Navštíveno`, takže aktuální poloha zůstává vždy jen jedna.

Veřejná stránka `/trasa` používá mapové dlaždice OpenStreetMap načítané přes Leaflet z CDN. Mapa není jediným nositelem informací: pod ní je vždy chronologická časová osa zastávek, přesunů, časů, médií a odkazů, použitelná se čtečkou i bez JavaScriptu.

Podrobný návod k zadávání cesty je v `TRASA_A_CASOVA_OSA.md`. Kompletní postup instalace a aktualizace je v `INSTALLACE.md`.

## Požadavky serveru

- PHP 8.3 nebo novější včetně rozšíření Ctype, cURL, DOM, Fileinfo, Mbstring, OpenSSL, PDO, Session, Tokenizer a XML,
- Composer 2,
- MariaDB nebo MySQL,
- nginx nebo Apache,
- cron.

## První instalace

```bash
cd /opt/notm/apps/slepe-slunce
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

Upravte databázi, `APP_URL` a odesílání pošty v `.env`, potom spusťte:

```bash
php artisan migrate --seed --force
php artisan storage:link
php artisan app:create-admin
php artisan optimize
```

Nginx musí mít kořen webu nastavený na adresář `public`, nikdy na kořen projektu. Vzor je v `deploy/nginx.conf.example`.

Adresáře pro zápis:

```bash
chown -R www-data:www-data storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;
```

## Plánované publikování

Do cronu přidejte:

```cron
* * * * * www-data cd /opt/notm/apps/slepe-slunce && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

## Nahrávání fotografií

V PHP nastavte alespoň `upload_max_filesize = 16M` a `post_max_size = 20M`. V nginx vzorové konfiguraci už je `client_max_body_size 20M`.

## Běžná aktualizace

```bash
php artisan down
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
php artisan up
```

## Ověření ve vývojovém prostředí

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan test
php artisan serve
```

Pro lokální SQLite změňte v `.env` `DB_CONNECTION=sqlite` a `DB_DATABASE` na absolutní cestu k souboru.

## Přístupnost

Veřejná část používá sémantické nadpisy a oblasti, odkaz pro přeskočení navigace, viditelný focus, vysoký kontrast, ovládání bez myši, respektování `prefers-reduced-motion`, popisy obrázků, názvy videí a volitelné přepisy. Každý nový příspěvek by měl být před zveřejněním zkontrolován klávesnicí a čtečkou obrazovky.
