# Slepé Slunce pro Android

Samostatný Android projekt. Aplikace používá zabezpečený WebView nad `https://slepeslunce.cz`, takže přihlášení, oprávnění a ukládání míst, poloh a fotografií jsou stejné jako na webu. V aplikaci jsou rychlé záložky Nástěnka, Místo, Fotka, Správa, Poloha a Mapa.

## Sestavení

Otevřete složku `android-app/` v Android Studiu s JDK 17 a Android SDK 36. Projekt používá Android Gradle Plugin 8.13.2 a Gradle 8.13. CI sestavuje debug APK příkazem `gradle :app:assembleDebug` a přikládá jej jako artefakt workflow `Tests`.

## Použití

1. Spusťte aplikaci a přihlaste se existujícím účtem Slepého Slunce. Přihlášení se drží v cookies Android WebView.
2. **Místo:** vyberte expedici, pojmenujte místo a načtěte GPS nebo souřadnice vyplňte. Pro přidávání bodů musí účet mít oprávnění publikovat (`admin` nebo `editor`).
3. **Fotka:** vyberte expedici, pořiďte snímek kamerou nebo vyberte z galerie, zadejte alternativní popis a případný krátký příběh. Povolte polohu, zadejte souřadnice nebo vyberte bod v mapě.
4. **Poloha:** odešlete GPS hlášení pro vybranou expedici. Systém uchovává historii podle nastavení archivace a retenční doby expedice.
5. **Správa:** upravte fotografii nebo místo, datum, popis, souřadnice a expedici. Přesun místa s navázanými úseky trasy vyžaduje nejprve úpravu těchto úseků v administraci.

## Aktualizace přes web

Aplikace při spuštění a nejvýše jednou za 24 hodin po návratu do popředí kontroluje `https://slepeslunce.cz/app/version.json`. Když server nabídne vyšší `version_code`, zobrazí výzvu k otevření `/app`. Instalaci APK potvrdí uživatel v Androidu. Při nedostupnosti sítě aplikace funguje dál a kontrolu zopakuje příště.

Po sestavení podepsané verze zkopírujte APK na server. Například z počítače s Windows v PowerShellu (nahraďte adresu serveru a cestu ke staženému souboru):

```powershell
scp "$env:USERPROFILE\Downloads\slepe-slunce-0.2.0-debug.apk" root@ADRESA_SERVERU:/tmp/slepe-slunce-0.2.0.apk
```

Na serveru ověřte soubor a zveřejněte jej:

```bash
ls -lh /tmp/slepe-slunce-0.2.0.apk
cd /opt/notm/apps/slepeslunce
php artisan app:publish-android /tmp/slepe-slunce-0.2.0.apk --version-code=2 --version-name=0.2.0
```

Hodnoty musí odpovídat `versionCode` a `versionName` v `app/build.gradle.kts`. Nové APK musí být podepsané stejným klíčem jako instalovaná aplikace. Debug APK z CI používá podpis pro testování, který se mezi běhy může lišit. Při přechodu z první testovací verze ji případně odinstalujte a nainstalujte novou; serverová data zůstanou v účtu. Pro trvalou distribuci uchovejte privátní keystore mimo git a sestavujte `gradle :app:assembleRelease` s proměnnými `ANDROID_KEYSTORE_PATH`, `ANDROID_KEYSTORE_PASSWORD`, `ANDROID_KEY_ALIAS` a `ANDROID_KEY_PASSWORD`. Tentýž klíč použijte pro všechny další verze a zvyšujte `versionCode`. Soubor APK se ukládá do sdíleného `storage/app/public/app/`, který deploy nemaže; manifest verze do soukromého storage. `/app` je veřejná stránka pro stažení.

Aplikace nepotřebuje oprávnění pro přístup k celé galerii; systémový výběr zpřístupní pouze vybranou fotografii. GPS oprávnění žádá až při otevření webové geolokace. WebView načítá pouze HTTPS doménu `slepeslunce.cz`; cizí odkazy otevírá v běžném prohlížeči. Kamera ukládá do interního prostoru aplikace pro následný upload. Zařízení musí být připojené k internetu, protože formuláře používají současný server.

Tato verze je funkční hybridní aplikace, ne offline klient. Před distribucí do Google Play je potřeba doplnit podpis vydání, grafické ikony, zásady ochrany soukromí a otestovat kameru, výběr souborů a GPS na fyzickém zařízení. Nepublikujte debug APK do obchodu.
