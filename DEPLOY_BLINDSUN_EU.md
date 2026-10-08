# Nasazení anglické vrstvy na blindsun.eu

`blindsun.eu` používá stejnou Laravel aplikaci jako `slepeslunce.cz`. Nginx pouze předá správný host; aplikace na hostu `blindsun.eu` nastaví angličtinu. Český web ani databáze se nekopírují.

## 1. DNS a certifikát

Doména a `www` musí směřovat na stejný server jako `slepeslunce.cz`. Certifikát musí pokrývat `blindsun.eu` i `www.blindsun.eu` (TLS se ověřuje před přesměrováním). Před konfigurací ověřte SAN a cesty:

```bash
sudo certbot certificates
```

## 2. Nginx

1. Zkopírujte [deploy/nginx.blindsun.eu.conf.example](deploy/nginx.blindsun.eu.conf.example) do `/etc/nginx/sites-available/blindsun.eu`.
2. Zkontrolujte zejména PHP socket. Tento server používá `/run/php/php8.5-fpm.sock`.
3. Aktivujte konfiguraci a ověřte syntaxi:

```bash
sudo ln -s /etc/nginx/sites-available/blindsun.eu /etc/nginx/sites-enabled/blindsun.eu
sudo nginx -t
sudo systemctl reload nginx
```

Konfiguraci pro `slepeslunce.cz` neměňte a domény nespojujte do jednoho `server_name`: samostatný blok zaručí, že se holá doména vždy kanonicky přesměruje na `https://www.blindsun.eu`.

## 3. Aplikační konfigurace

Po nasazení větve doplňte do produkčního `.env`:

```dotenv
CZECH_SITE_URL=https://slepeslunce.cz
ENGLISH_SITE_URL=https://www.blindsun.eu
ENGLISH_SITE_HOSTS=blindsun.eu,www.blindsun.eu
```

Poté obnovte cache konfigurace:

```bash
cd /opt/notm/apps/slepeslunce
php artisan optimize:clear
php artisan optimize
```

## 4. Kontrola

```bash
curl -I http://blindsun.eu
curl -I https://www.blindsun.eu
curl -s https://www.blindsun.eu | grep '<html lang="en">'
curl -s https://slepeslunce.cz | grep '<html lang="cs">'
```

Očekávaný výsledek: HTTP i holá doména přesměrují na `https://www.blindsun.eu`; veřejný HTML dokument na nové doméně má `lang="en"`.

## Jak funguje obsah

Domovská stránka a navigace mají anglickou vrstvu. Příspěvky, expedice a návody překládá interní LibreTranslate podle [TRANSLATIONS.md](TRANSLATIONS.md).

Až budeme připravovat anglické redakční překlady článků, uložíme je přímo v administraci. Tím se jejich vlastní URL, metadata a SEO stanou plnohodnotně anglickými.

Na serveru zjistěte aktivní konfiguraci: `sudo nginx -T | grep -n -A 12 -B 4 'server_name.*blindsun.eu'`. Dále otestujte obě HTTPS varianty pomocí `curl -IL https://blindsun.eu/` a `curl -IL https://www.blindsun.eu/`. Pokud `www` chybí v DNS či certifikátu, nejprve opravte DNS a vystavte certifikát pro obě jména. Úprava souboru v repozitáři sama nginx na serveru nepřenastaví.
