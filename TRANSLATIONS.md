# Překlady webu bez externího API

Čeština je zdrojový jazyk. `www.blindsun.eu` zobrazuje anglickou verzi stejné Laravel aplikace a databáze. Překlady článků, návodů a expedic se ukládají do `content_translations`.

## Interní LibreTranslate

Na stejném serveru použijte [deploy/libretranslate.compose.yml](deploy/libretranslate.compose.yml). Port je navázaný jen na loopback `127.0.0.1:5000`, modely `cs,en` jsou v trvalém Docker volume. Před spuštěním ověřte, že Docker Compose funguje a port 5000 je volný:

```bash
docker compose version
ss -ltn '( sport = :5000 )'
cd /opt/notm/apps/slepeslunce
docker compose -f deploy/libretranslate.compose.yml up -d
docker compose -f deploy/libretranslate.compose.yml logs --tail=50
```

První stažení modelů a start může trvat déle. Ověřte, že `GET /languages` nabízí překlad `cs → en`.

Produkční `.env`:

```dotenv
CZECH_SITE_URL=https://slepeslunce.cz
ENGLISH_SITE_URL=https://www.blindsun.eu
ENGLISH_SITE_HOSTS=blindsun.eu,www.blindsun.eu
LIBRETRANSLATE_URL=http://127.0.0.1:5000
LIBRETRANSLATE_TIMEOUT=60
DB_QUEUE_RETRY_AFTER=900
```

Ověření lokálního API:

```bash
curl -fsS http://127.0.0.1:5000/languages
curl -fsS http://127.0.0.1:5000/translate -H 'Content-Type: application/json' -d '{"q":"Cestujeme spolu.","source":"cs","target":"en","format":"text"}'
php artisan migrate --force
php artisan optimize:clear
```

Administrace po vytvoření i úpravě článku, návodu nebo expedice zařadí překlad do Laravel fronty. Je nutné mít spuštěný queue worker. Na editační obrazovce lze překlad znovu zařadit nebo ručně upravit; ručně upravený překlad se dalším automatickým během nepřepíše. Po změně českého originálu lze překlad zkontrolovat a uložit znovu.

Zpětný překlad existujícího obsahu:

```bash
php artisan content:translate-existing
```

Příkaz přeskočí aktuální a redakčně upravené překlady, změněný dosud neschválený obsah přeloží znovu. `--fresh` výslovně přepíše všechny překlady včetně redakčně schválených. Při výpadku interní služby hlásí neúspěšné položky a skončí chybovým kódem; překlady již uložené v databázi zůstanou zachované.

Strojový překlad před propagací ověřte u textů o lidech, asistenci a bezpečnosti. Překlad rozhraní a dalšího obsahu (mapy, popisky fotek, formuláře) vyžaduje samostatnou redakční kontrolu.

Databázová fronta musí mít `DB_QUEUE_RETRY_AFTER` vyšší než maximální doba překladu (úloha má timeout 600 s). Po změně `.env` spusťte `php artisan optimize:clear` a restartujte `slepe-slunce-queue.service`. Na hostu nevystavujte port 5000 veřejně.
