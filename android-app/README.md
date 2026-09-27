# Slepé Slunce pro Android

Samostatný Android projekt. První verze používá zabezpečený WebView nad `https://slepeslunce.cz`, takže přihlášení, oprávnění a ukládání míst, poloh a fotografií jsou stejné jako na webu. V aplikaci jsou rychlé záložky Nástěnka, Místo, Fotka, Poloha a Mapa.

## Sestavení

Otevřete složku `android-app/` v Android Studiu s JDK 17 a Android SDK 36. Projekt používá Android Gradle Plugin 8.13.2 a Gradle 8.13. CI sestavuje debug APK příkazem `gradle :app:assembleDebug` a přikládá jej jako artefakt workflow `Tests`.

## Použití

1. Spusťte aplikaci a přihlaste se existujícím účtem Slepého Slunce. Přihlášení se drží v cookies Android WebView.
2. **Místo:** vyberte expedici, pojmenujte místo a načtěte GPS nebo souřadnice vyplňte. Pro přidávání bodů musí účet mít oprávnění publikovat (`admin` nebo `editor`).
3. **Fotka:** vyberte expedici, pořiďte snímek kamerou nebo vyberte z galerie, zadejte alternativní popis a případný krátký příběh. Povolte polohu, zadejte souřadnice nebo vyberte bod v mapě.
4. **Poloha:** odešlete GPS hlášení pro vybranou expedici. Systém uchovává historii podle nastavení archivace a retenční doby expedice.

Aplikace nepotřebuje oprávnění pro přístup k celé galerii; systémový výběr zpřístupní pouze vybranou fotografii. GPS oprávnění žádá až při otevření webové geolokace. WebView načítá pouze HTTPS doménu `slepeslunce.cz`; cizí odkazy otevírá v běžném prohlížeči. Kamera ukládá do interního prostoru aplikace pro následný upload. Zařízení musí být připojené k internetu, protože formuláře používají současný server.

Tato verze je funkční hybridní aplikace, ne offline klient. Před distribucí do Google Play je potřeba doplnit podpis vydání, grafické ikony, zásady ochrany soukromí a otestovat kameru, výběr souborů a GPS na fyzickém zařízení. Nepublikujte debug APK do obchodu.
