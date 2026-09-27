# Sentry na veřejném webu

Veřejný web načítá Sentry Browser Loader pouze v případě, že je nastavená proměnná `SENTRY_BROWSER_LOADER_URL` s adresou vlastního projektu na `https://js.sentry-cdn.com/…min.js`. URL získáte v Sentry v nastavení projektu, v části Client Keys / Loader Script. Nepoužívejte URL ani veřejný klíč jiného projektu.

Do produkčního `.env` přidejte:

```dotenv
SENTRY_BROWSER_LOADER_URL=https://js.sentry-cdn.com/VLASTNI_VEREJNY_KLIC.min.js
SENTRY_BROWSER_TRACES_SAMPLE_RATE=0.1
```

Po změně konfigurace spusťte `php artisan config:cache` nebo restartujte proces, který konfiguraci drží v paměti. V projektu Sentry zapněte Browser Tracing pro Loader Script. Hodnota 0.1 vzorkuje přibližně 10 % návštěv; hodnota 0 měření výkonu vypne. Chyby JavaScriptu se hlásí bez této vzorkovací podmínky. Session Replay a výchozí sběr osobních údajů jsou vypnuté. Nevkládejte do Sentry souřadnice, texty formulářů ani jiné osobní údaje jako vlastní kontext.

Ověřte v prohlížeči načtení loaderu a v Sentry testovací chybu vyvolanou **pouze v testovacím prostředí**. Chyby PHP na serveru tato konfigurace neodesílá; pro jejich sběr je nutné samostatně instalovat oficiální `sentry/sentry-laravel` SDK a aktualizovat `composer.lock` v prostředí s Composerem. Standardní Laravel logging zůstává aktivní.
