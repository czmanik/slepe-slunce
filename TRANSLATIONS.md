# Překlady webu bez externího API

Čeština je zdrojový jazyk. `www.blindsun.eu` zobrazuje anglickou verzi stejné Laravel aplikace a databáze. Překlady článků, návodů a expedic se ukládají do `content_translations`.

## Interní LibreTranslate

Nainstalujte LibreTranslate na stejný server, přístupný pouze přes loopback `127.0.0.1:5000`; nevystavujte port internetu. Nainstalujte jazykový pár `cs → en` a ověřte, že jej `GET /languages` nabízí. Pro Docker lze použít image `libretranslate/libretranslate`, omezit jazyky volbou `--load-only cs,en`, svázat port `127.0.0.1:5000:5000` a připojit trvalý volume pro stažené modely. Konkrétní parametry image ověřte při instalaci proti zvolené verzi.

Produkční `.env`:

```dotenv
CZECH_SITE_URL=https://slepeslunce.cz
ENGLISH_SITE_URL=https://www.blindsun.eu
ENGLISH_SITE_HOSTS=blindsun.eu,www.blindsun.eu
LIBRETRANSLATE_URL=http://127.0.0.1:5000
LIBRETRANSLATE_TIMEOUT=60
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
