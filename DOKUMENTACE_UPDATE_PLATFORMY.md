# Dokumentace updatu platformy Slepé Slunce

Tento dokument popisuje update, který mění původní jednorázový expediční deník na opakovatelnou platformu pro více expedic, veřejné přihlášky, tematické odběry a prodej archivních vín.

Dokument odpovídá větvi `agent/multi-expedition-commerce-plan` a pull requestu #3.

## 1. Přehled změn

Update přidává:

- zastřešující web projektu Slepé Slunce;
- samostatné miniweby jednotlivých expedic;
- automatický nebo ručně nastavený stav expedice;
- oddělené členy, články, trasu, program, fotografie a GPS polohy pro každou expedici;
- znovu použitelný katalog fyzických míst;
- bezpečnější model bodů a propojení trasy;
- konfigurovatelné přihlášky a kapacitu expedice;
- evidenci schválení, záloh, plateb a slev u účastníků;
- tematické odběry novinek s double opt-in;
- denní urgentní a týdenní standardní rozesílky;
- základ e-shopu s archivními víny, skladem a hostovskou objednávkou;
- napojení na platební bránu Comgate;
- evidenci objednávek, plateb a daňových dokladů;
- vlastní tlačítko pro hlasité přečtení článku;
- veřejné stránky navržené pro WCAG 2.2 AA a běžné čtečky obrazovky.

## 2. Přednastavená data a účty

### 2.1 Výchozí expedice

Při migraci se automaticky založí:

| Pole | Hodnota |
|---|---|
| Název | Slepé Slunce 2026 |
| Adresa | `slepe-slunce-2026` |
| Začátek | 10. srpna 2026 00:00 |
| Konec | 16. srpna 2026 23:59 |
| Stav publikace | Veřejná |
| Hlavní expedice | Ano |
| Přihlášky | Vypnuté |
| Archivace GPS | Zapnutá |

Existující články, body trasy, přesuny, fotografie, GPS polohy a stav expedice se při migraci přiřadí k této expedici. Obecné články lze po migraci od expedice odpojit a publikovat je jako obsah celého projektu.

### 2.2 Výchozí autor

Seeder zakládá autorský profil:

- **Mirek Mužík**;
- popis: „Spoluautor projektu Slepé Slunce.“;
- označení člena expedice: ano.

Autorský profil není přihlašovací účet. Nemá e-mail ani heslo a nelze se jím přihlásit do administrace.

### 2.3 Přihlašovací účty

Projekt z bezpečnostních důvodů neobsahuje žádný univerzální přednastavený e-mail ani heslo.

Existující účty se migrací zachovají. Prvního nebo náhradního správce lze vytvořit interaktivně:

```bash
php artisan app:create-admin
```

Příkaz se zeptá na e-mail, jméno a heslo. Heslo musí mít alespoň 12 znaků. Pokud účet se zadaným e-mailem již existuje, bude aktualizován na aktivního správce.

Alternativně lze účet připravit přes `.env`:

```dotenv
ADMIN_NAME="Správce"
ADMIN_EMAIL="spravce@example.cz"
ADMIN_PASSWORD="vlastni-dlouhe-heslo"
```

A následně spustit:

```bash
php artisan db:seed --force
```

Deploy skript seeder automaticky nespouští. Hodnoty `ADMIN_*` jsou volitelné a nesmí se commitovat s reálným heslem.

### 2.4 Uživatelské role

| Role | Určení | Hlavní oprávnění |
|---|---|---|
| Správce | vlastník systému | kompletní administrace, účty, mazání a obnova obsahu |
| Editor | vedoucí/redaktor | publikování, správa článků, autorů, bodů a přesunů; omezené mazání |
| Autor | člen týmu | přístup do panelu, vlastní články, hlášení polohy a přidání fotografie |

Do administrace se může přihlásit pouze aktivní účet. Účty spravuje správce v sekci **Uživatelé**. Minimální délka hesla v administračním formuláři je 12 znaků.

## 3. Veřejná struktura webu

### 3.1 Hlavní web

Hlavní web představuje Slepé Slunce jako společný projekt. Obsahuje hlavní expedici, proběhlé expedice, plánované expedice, obecný deník a odběr novinek.

Zachované původní adresy:

- `/` – hlavní stránka;
- `/denik` – obecný a původní deník;
- `/trasa` – zpětně kompatibilní přesměrování/zobrazení původní expedice;
- `/clenove` – členové původní expedice;
- `/sitemap.xml` – mapa webu.

### 3.2 Web expedice

Každá zveřejněná expedice má vlastní adresy:

- `/expedice/{slug}` – detail expedice;
- `/expedice/{slug}/program-a-trasa` – program, textová časová osa a mapa;
- `/expedice/{slug}/denik` – články expedice;
- `/expedice/{slug}/clenove` – tým expedice;
- `/expedice/{slug}/prihlaska` – přihláška, pokud je povolená.

Menu uvnitř expedice je oddělené od hlavního menu projektu.

### 3.3 Obchod

V produkci obchod běží na doméně nastavené v `SHOP_DOMAIN`, výchozí hodnota je:

```text
https://shop.slepeslunce.cz
```

V lokálním a testovacím prostředí je obchod dostupný na `/obchod`.

## 4. Správa expedic

Nová expedice se zakládá v administraci v sekci **Expedice → Expedice**.

### 4.1 Základní nastavení

U expedice se nastavuje:

- název a URL slug;
- krátký a podrobný popis;
- datum a čas začátku a konce;
- koncept nebo veřejná expedice;
- hlavní expedice pro homepage;
- volitelný ruční stav;
- hlavní fotografie a povinný alternativní text.

Bez ručního přepsání se stav počítá podle termínu:

- před začátkem: plánovaná;
- mezi začátkem a koncem: právě probíhá;
- po skončení: dokončená.

Ruční stav lze nastavit na plánovanou, aktivní, dokončenou nebo archivní.

### 4.2 Organizace

Každá expedice může mít vlastní:

- vedoucího;
- kontaktní e-mail a telefon;
- nástupní místa;
- informace o dopravě;
- ubytování;
- popis přístupnosti a asistence;
- seznam služeb v ceně;
- storno podmínky;
- minimální počet účastníků.

### 4.3 Tým expedice

Členství není globální. Jeden autor může být členem více expedic a v každé mít jinou roli, pořadí, medailonek a příznak vedoucího.

Autorské profily se spravují v **Autoři**, konkrétní členství také přímo v editaci expedice.

## 5. Program, místa a trasa

### 5.1 Oddělení místa a návštěvy

`locations` je katalog fyzických míst se souřadnicemi. `route_points` představuje konkrétní návštěvu místa v určité expedici. Díky tomu lze Prahu nebo Mikulov použít opakovaně v různých cestách a časech.

### 5.2 Body a přesuny

Každý bod a každý přesun patří právě k jedné expedici. Přesun musí:

- spojovat dva různé body;
- spojovat body stejné expedice;
- mít chronologicky platný plánovaný nebo skutečný čas;
- používat zvolený typ dopravy.

Silniční geometrie pro auto a autobus se načítá přes OSRM a ukládá do databáze. Let se vykresluje lokálně jako oblouk po velké kružnici a nepotřebuje externí routing.

Přepočet všech geometrií:

```bash
php artisan route:recalculate-geometries
```

### 5.3 Jednotná časová osa

`program_items` spojuje zastávky, přesuny a samostatné aktivity. Podporované typy zahrnují:

- zastávku;
- přesun;
- aktivitu;
- ubytování;
- ochutnávku;
- jídlo;
- volný program.

Program se spravuje v **Expedice → Program**. Mapa má vždy textovou alternativu v podobě časové osy.

## 6. Přihlášky na expedice

Přihlášky se zapínají u konkrétní expedice. Lze povolit jednu nebo více variant:

- nezávazný zájem;
- žádost o účast;
- rezervace místa.

Nastavit lze:

- celkovou kapacitu;
- počet míst pro veřejnost;
- otevření a uzavření přihlášek;
- cenu v CZK a EUR;
- minimální počet účastníků;
- dobu blokace místa, výchozí 48 hodin.

Administrátor u žádosti eviduje stav přihlášky, stav platby, částku k úhradě, zaplacenou částku, slevu a datum konce rezervace.

Stavy přihlášky:

- nová;
- schválená – čeká na úhradu;
- čekací listina;
- potvrzená;
- zamítnutá;
- zrušená;
- propadlá.

Stavy platby:

- nezaplaceno;
- zaplacena záloha;
- zaplaceno;
- sleva.

Schválené nezaplacené rezervace po nastavené lhůtě automaticky propadnou.

## 7. Články a hlasité čtení

Článek může být:

- obecný pro celý projekt;
- přiřazený ke konkrétní expedici.

U článku se nastavuje také režim rozeslání:

- nerozesílat;
- týdenní přehled;
- nejbližší urgentní denní přehled.

Veřejný detail článku obsahuje tlačítko **Přečíst článek nahlas**, které používá syntézu řeči prohlížeče. Struktura stránky současně zůstává použitelná s běžnými čtečkami obrazovky; vlastní tlačítko není náhradou sémantického HTML.

## 8. Odběr novinek

Odběr používá double opt-in:

1. návštěvník odešle formulář;
2. vznikne čekající záznam;
3. návštěvník potvrdí e-mail unikátním odkazem;
4. teprve potom je odběr aktivní.

Témata:

- život projektu a obecné články;
- nové expedice;
- obchod s víny;
- jedna nebo více konkrétních expedic.

Nepotvrzené odběry se mažou po jednom měsíci. Odběratel má vlastní odhlašovací odkaz. Volitelná synchronizace do Mailchimpu se zapne doplněním jeho konfigurace.

Rozesílky:

- urgentní souhrn každý den v 08:00;
- standardní týdenní souhrn v pondělí v 09:00.

Ruční spuštění:

```bash
php artisan subscriptions:send urgent
php artisan subscriptions:send weekly
```

## 9. E-shop a sklad vín

Současná verze používá oddělený nativní Laravel modul. Stabilní Lunar 1.3 nepodporuje Laravel 13 a Lunar 2 je zatím nestabilní; přechod na stabilní Lunar 2 zůstává možný bez změny veřejných URL a Comgate adaptéru.

### 9.1 Katalog

Produkt představuje víno. Varianty představují konkrétní ročník, jakost nebo velikost lahve.

U varianty se eviduje:

- unikátní SKU;
- ročník;
- objem lahve;
- jakost;
- cena v haléřích a volitelně v eurocentech;
- sazba DPH;
- fyzický sklad;
- rezervované množství;
- aktivní prodej.

Příklad: cena `125000` znamená `1 250,00 Kč`.

### 9.2 Objednávka

Nákup nevyžaduje zákaznický účet. Zákazník:

1. potvrdí věk alespoň 18 let;
2. vloží dostupné lahve do košíku;
3. zadá fakturační údaje;
4. přijme podmínky a zpracování údajů;
5. odešle objednávku;
6. je přesměrován na Comgate, pokud je brána nakonfigurovaná.

Při vytvoření objednávky se zboží rezervuje. Nezaplacená nová objednávka starší než dva dny se automaticky zruší a rezervace skladu se uvolní.

Aktuálně je připraven osobní odběr. Dopravce, cena dopravy a přesné místo odběru jsou otevřené provozní body před ostrým spuštěním.

### 9.3 Doklady

Objednávka uchovává položky, ceny, DPH, měnu, zákazníka, stav platby, platební záznamy a číslo dokladu. Doklad je dostupný přes neveřejný tokenizovaný odkaz.

Před ostrým prodejem je nutné doplnit DIČ, fakturační adresu, číselné řady, obchodní podmínky, ochranu osobních údajů, reklamační podmínky a ověřit účetní náležitosti.

## 10. Comgate

Konfigurace:

```dotenv
COMGATE_MERCHANT=
COMGATE_SECRET=
COMGATE_TEST=true
```

Testovací režim ponechte zapnutý až do dokončení celé testovací objednávky, callbacku, návratu zákazníka a případného refundu.

Produkční callback:

```text
https://shop.slepeslunce.cz/platba/comgate/callback
```

Implementace neuznává platbu jen podle návratové URL v prohlížeči. Stav ověřuje serverovým dotazem vůči Comgate a zpracování callbacku je navrženo jako opakovatelné.

## 11. Konfigurace prostředí

Minimální nové nebo důležité hodnoty v `.env`:

```dotenv
APP_URL=https://slepeslunce.cz
APP_TIMEZONE=Europe/Prague

MAIL_MAILER=smtp
MAIL_HOST=smtp.seznam.cz
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=news@slepeslunce.cz
MAIL_FROM_NAME="Slepé Slunce"

SHOP_DOMAIN=shop.slepeslunce.cz
SHOP_SELLER_NAME="Heaven's Mill CZ s.r.o."
SHOP_SELLER_ICO=04561929
SHOP_SELLER_DIC=

COMGATE_MERCHANT=
COMGATE_SECRET=
COMGATE_TEST=true

MAILCHIMP_API_KEY=
MAILCHIMP_SERVER=
MAILCHIMP_LIST_ID=
```

Přesné SMTP proměnné je nutné přizpůsobit aktuálním požadavkům Seznam.cz. Po změně `.env` spusťte:

```bash
php artisan optimize:clear
php artisan config:cache
```

## 12. Nginx a DNS

DNS záznam `shop.slepeslunce.cz` musí směřovat na stejný server jako hlavní web. Nginx musí obsluhovat všechny tři názvy:

```nginx
server_name slepeslunce.cz www.slepeslunce.cz shop.slepeslunce.cz;
```

Vzor je v `deploy/nginx.conf.example`. Po změně:

```bash
sudo nginx -t
sudo systemctl reload nginx
```

SSL certifikát musí obsahovat i `shop.slepeslunce.cz`.

## 13. Scheduler a fronta

Na serveru musí každou minutu běžet Laravel scheduler:

```cron
* * * * * www-data cd /opt/notm/apps/slepe-slunce && /usr/bin/php artisan schedule:run >> /opt/notm/apps/slepe-slunce/storage/logs/scheduler.log 2>&1
```

Scheduler zajišťuje:

- publikaci naplánovaných článků;
- urgentní a týdenní rozesílky;
- mazání nepotvrzených odběrů;
- expiraci rezervací expedic;
- retenci GPS podle nastavení expedice;
- zrušení starých nezaplacených objednávek a uvolnění skladu.

Deploy skript připravuje také systemd službu `slepe-slunce-queue.service`.

## 14. Nasazení updatu

### 14.1 Doporučený automatický postup

Na vývojovém serveru pro současný draft:

```bash
cd /opt/notm/apps/slepeslunce-dev
sudo ./deploy.sh agent/multi-expedition-commerce-plan
```

Po sloučení do produkční větve:

```bash
cd /opt/notm/apps/slepeslunce
sudo ./deploy.sh main
```

Deploy skript:

- odmítne nasazení přes lokálně změněný pracovní strom;
- vytvoří databázovou zálohu, pokud je dostupný `mariadb-dump` nebo `mysqldump`;
- zapne maintenance režim;
- nainstaluje závislosti z `composer.lock`;
- spustí migrace;
- vygeneruje náhledy;
- obnoví cache;
- nastaví oprávnění;
- vytvoří nebo restartuje queue worker a cron;
- ověří stav migrací a služby.

### 14.2 Ruční minimální postup

```bash
git pull --ff-only
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
php artisan down
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan optimize
php artisan queue:restart
php artisan up
```

Před ruční migrací vždy vytvořte databázovou zálohu.

## 15. Obnova po chybě MySQL 1553

Původní verze updatu mohla skončit chybou:

```text
Cannot drop index 'member_locations_user_id_unique': needed in a foreign key constraint
```

Opravená migrace:

1. nejprve přidá běžný index pro cizí klíč `user_id`;
2. odstraní původní unikátní index;
3. přidá vazbu na expedici;
4. vytvoří unikátnost dvojice `expedition_id + user_id`;
5. pozná tabulky a sloupce vytvořené před předchozím pádem a pokračuje bez jejich opětovného zakládání.

Po stažení opravy stačí:

```bash
git pull origin agent/multi-expedition-commerce-plan
php artisan optimize:clear
php artisan migrate --force
```

Nepoužívejte `php artisan migrate:fresh`, pokud databáze obsahuje skutečná data. Tento příkaz všechny tabulky smaže.

Pokud migrace skončí jinou chybou, nepokračujte ručním mazáním tabulek. Uchovejte výpis chyby a databázovou zálohu.

## 16. Kontrola po nasazení

### Technická kontrola

```bash
php artisan migrate:status
php artisan about
php artisan route:list --except-vendor
php artisan schedule:list
sudo systemctl status slepe-slunce-queue.service
sudo nginx -t
```

### Funkční kontrola

1. Přihlášení na `/admin`.
2. Otevření a uložení expedice Slepé Slunce 2026.
3. Kontrola původních článků, členů, bodů a fotografií.
4. Vytvoření neveřejné testovací expedice.
5. Přidání dvou bodů, přesunu a aktivity programu.
6. Kontrola, že body jiné expedice nelze propojit.
7. Test přihlášky a kapacity.
8. Test potvrzovacího e-mailu odběru.
9. Test produktu, varianty, skladu a rezervace objednávky.
10. Testovací platba Comgate pouze s `COMGATE_TEST=true`.
11. Kontrola webu klávesnicí a čtečkou obrazovky.

Automatické ověření vývojové verze:

```bash
php artisan test
```

Referenční stav updatu je 28 úspěšných testů a 117 kontrol.

## 17. Přístupnost

Veřejná část používá:

- sémantické nadpisy a landmarky;
- popisky formulářových polí;
- textové chybové zprávy a `aria-invalid`;
- ovládání klávesnicí;
- viditelný focus;
- alternativní texty obrázků;
- textovou alternativu mapy;
- tlačítko hlasitého čtení článku;
- formuláře bez závislosti na barvě nebo pouze vizuální mapě.

Před ostrým spuštěním je stále nutný ruční audit WCAG 2.2 AA a uživatelský test s Mirkem nebo dalšími uživateli čteček obrazovky.

## 18. Otevřené body před ostrým spuštěním

- dokončení převodu společnosti Heaven's Mill CZ s.r.o.;
- doplnění DIČ a fakturační adresy;
- schválení obchodních, reklamačních, storno a GDPR textů;
- určení místa osobního odběru;
- rozhodnutí o dopravě vín po ČR a EU;
- produkční Comgate údaje a ověření testovací transakce;
- rozhodnutí o samostatné značce obchodu;
- pravidla rozdělení výtěžku mezi nadaci a provoz expedic;
- import a fyzická inventura přibližně 8 000 lahví a 150 variant;
- případné Mailchimp údaje a mapování audience;
- audit WCAG 2.2 AA s reálnými uživateli.

Dokud tyto body nejsou uzavřené, obchod má být provozován pouze v testovacím režimu.
