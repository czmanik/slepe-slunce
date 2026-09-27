# Sentry: chyby serveru, logy a veřejný web

## Laravel a PHP

Projekt používá oficiální `sentry/sentry-laravel` SDK. Instalace při nasazení probíhá běžným `composer install` z verzovaného `composer.lock`. V `bootstrap/app.php` se neobsloužené výjimky předávají přes `Sentry\Laravel\Integration::handles($exceptions)`; stávající nastavení proxy, přihlášení a CSRF zůstává platné.

DSN pro tento projekt (veřejný identifikátor; patří pouze do nastavení cílového serveru):

```dotenv
SENTRY_LARAVEL_DSN=https://a2fed10f408a8171cf8609f13b145597@o4511187073695744.ingest.de.sentry.io/4512157486809168
SENTRY_ENABLE_LOGS=true
LOG_CHANNEL=stack
LOG_STACK=single,sentry_logs
SENTRY_LOG_LEVEL=warning
```

`config/sentry.php` je součástí repozitáře a bere DSN z prostředí. `config/logging.php` obsahuje kanál **`sentry_logs`**: do Sentry posílá varování a závažnější záznamy; nižší úroveň lze nastavit pomocí `SENTRY_LOG_LEVEL=info`. V aplikaci použijte `Log::channel('sentry_logs')`, nikoli `Log::channel('sentry')`. `single` ponechává lokální souborový log. Pokud nechcete odesílat běžné logy, nastavte `LOG_STACK=single` a výjimky se budou dále hlásit samostatně přes Integration.

Po změně `.env` spusťte `php artisan config:cache` a restartujte PHP procesy i queue worker. Na testovacím prostředí ověřte odeslání pomocí `php artisan sentry:test` a zkontrolujte nový záznam v odpovídajícím projektu Sentry. Test neposílejte opakovaně z produkce. Nezapínejte `zend.exception_ignore_args=Off` bez posouzení dopadu na citlivé údaje: argumenty mohou obsahovat souřadnice a data formulářů.

## Prohlížeč

Pro chyby JavaScriptu a vzorkované měření výkonu slouží Sentry Browser Loader. V nastavení **téhož projektu** získáte adresu Loader Scriptu na `https://js.sentry-cdn.com/…min.js`. Jeho veřejné ID může být jiné než veřejný klíč v DSN. Do produkčního `.env` přidejte přesně adresu vygenerovanou v Sentry:

```dotenv
SENTRY_BROWSER_LOADER_URL=https://js.sentry-cdn.com/VLASTNI_VEREJNE_ID.min.js
SENTRY_BROWSER_TRACES_SAMPLE_RATE=0.1
```

V Sentry pro Loader Script zapněte Browser Tracing. Vzorkování 0.1 znamená přibližně 10 % návštěv pro měření výkonu; nula měření vypne. Hlášení chyb na tom nezávisí. Session Replay a výchozí sběr osobních údajů jsou vypnuté. Nevkládejte souřadnice, obsah formulářů ani jiné osobní údaje do vlastních Sentry tagů či kontextu.

Chyby prohlížeče a PHP se mohou zobrazit v témže projektu, ale jde o dva samostatné SDK a dvě oddělené konfigurace. Po aktivaci ověřte v testovacím prostředí událost na serveru i chybu JavaScriptu v prohlížeči.
