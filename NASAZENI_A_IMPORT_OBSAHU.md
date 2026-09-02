# Nasazení platformy a přenos stávajícího obsahu

Tento postup aktualizuje stávající instalaci bez mazání databáze. Přidá více expedic, veřejnou informaci o připravovaném Chorvatsku a skryje modul vín z veřejného webu. Nikdy nepoužívejte `migrate:fresh`.

## 1. Záloha a aktualizace kódu

V adresáři aplikace si nejprve ověřte stav repozitáře a proveďte zálohu databáze. Poté stáhněte větev s touto změnou a spusťte běžný deploy:

```bash
cd /opt/notm/apps/slepeslunce
git fetch --prune origin
git switch codex/public-site-with-test-commerce
git pull --ff-only origin codex/public-site-with-test-commerce
./deploy.sh codex/public-site-with-test-commerce
```

Deploy musí provést `php artisan migrate --force`; migrace přidají datový model více expedic, Valtice a záznam **Chorvatsko: moře bez bariér**. Chorvatsko se zobrazí jako připravované, bez termínu, ceny a přihlášky.

## 2. Zachování nebo přenos dosavadního obsahu

Při aktualizaci přímo ve stávající produkční instalaci se články, autoři a média nemažou: migrace je pouze přiřadí k expedici `slepe-slunce-2026`. Tento krok proto přeskočte.

Následující import použijte jen při přenosu obsahu ze stávající produkce do samostatné nové nebo vývojové instalace. Příkazy spusťte v **cílové** instalaci; zdrojový adresář upravte podle skutečné produkční cesty. Nejprve si zobrazte náhled. Příkaz zdrojovou instalaci ani její databázi nemění:

```bash
php artisan app:import-production-content \
  --source-env=/opt/notm/apps/slepeslunce/.env \
  --source-storage=/opt/notm/apps/slepeslunce/storage/app/public \
  --owner=ADMIN_EMAIL
```

`ADMIN_EMAIL` nahraďte e-mailem existujícího správce v nové instalaci. Je-li náhled správný, zopakujte příkaz s `--apply`:

```bash
php artisan app:import-production-content \
  --source-env=/opt/notm/apps/slepeslunce/.env \
  --source-storage=/opt/notm/apps/slepeslunce/storage/app/public \
  --owner=ADMIN_EMAIL \
  --apply
php artisan app:generate-post-thumbnails
php artisan optimize:clear
```

Import převádí pouze autory, články, jejich vazby a soubory médií. Nepřenáší uživatele, hesla, odběratele, objednávky ani platby. Články přiřadí k expedici `slepe-slunce-2026`.

## 3. Test Comgate bez zveřejnění modulu vín

Do `.env` vložte skutečné testovací údaje Comgate a ponechte testovací režim:

```dotenv
SHOP_TESTING_ENABLED=true
COMGATE_MERCHANT=
COMGATE_SECRET=
COMGATE_TEST=true
```

Poté spusťte `php artisan optimize:clear`. Po přihlášení správce na `/admin` lze testovat na `https://slepeslunce.cz/obchod`; v navigaci ani odběru novinek se modul neobjeví. Callback Comgate je `https://slepeslunce.cz/obchod/platba/comgate/callback`.

Po testu nastavte `SHOP_TESTING_ENABLED=false` a znovu proveďte `php artisan optimize:clear`.
