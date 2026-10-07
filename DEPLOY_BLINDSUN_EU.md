# Nasazení anglické vrstvy na blindsun.eu

`blindsun.eu` používá stejnou Laravel aplikaci jako `slepeslunce.cz`. Nginx pouze předá správný host; aplikace na hostu `blindsun.eu` nastaví angličtinu. Český web ani databáze se nekopírují.

## 1. DNS a certifikát

Doména a `www` musí směřovat na stejný server jako `slepeslunce.cz`. Certifikát je už vystavený; před konfigurací ověřte jeho přesné cesty:

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

Konfiguraci pro `slepeslunce.cz` neměňte a domény nespojujte do jednoho `server_name`: samostatný blok zaručí, že se `www.blindsun.eu` vždy kanonicky přesměruje na `https://blindsun.eu`.

## 3. Aplikační konfigurace

Po nasazení větve doplňte do produkčního `.env`:

```dotenv
CZECH_SITE_URL=https://slepeslunce.cz
ENGLISH_SITE_URL=https://blindsun.eu
ENGLISH_SITE_HOSTS=blindsun.eu,www.blindsun.eu
```

Poté obnovte cache konfigurace:

```bash
cd /opt/notm/apps/slepe-slunce
php artisan optimize:clear
php artisan optimize
```

## 4. Kontrola

```bash
curl -I http://blindsun.eu
curl -I https://www.blindsun.eu
curl -s https://blindsun.eu | grep '<html lang="en">'
curl -s https://slepeslunce.cz | grep '<html lang="cs">'
```

Očekávaný výsledek: HTTP i `www` přesměrují na `https://blindsun.eu`; veřejný HTML dokument na nové doméně má `lang="en"`.

## Jak funguje obsah

Domovská stránka a navigace mají anglickou vrstvu. Příspěvky, expedice a návody, které zatím nemají redakční anglický překlad, jasně nabídnou odkaz na automatický překlad aktuální stránky přes Google Translate. Tento odkaz nepřenáší do aplikace žádný Google API klíč a nemění uložený obsah.

Až budeme připravovat anglické redakční překlady článků, uložíme je přímo v administraci. Tím se jejich vlastní URL, metadata a SEO stanou plnohodnotně anglickými.
