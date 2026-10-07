# Redakční anglické překlady

Překlady se ukládají do tabulky `content_translations`. Na `blindsun.eu` se zobrazí automaticky, na české doméně zůstává zdrojový český text. Chybějící překlad bezpečně použije český originál.

## Nastavení Google Cloud

V projektu Google Cloud aktivujte **Cloud Translation API**, vytvořte omezený API klíč pro Translation API a do produkčního `.env` vložte:

```dotenv
GOOGLE_TRANSLATE_API_KEY=...
GOOGLE_TRANSLATE_TIMEOUT=20
```

Klíč nikdy nedávejte do GitHubu ani do JavaScriptu. Poté:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan content:translate-existing
php artisan optimize
```

Příkaz vytvoří anglické verze všech článků a expedic. Pro vědomé přegenerování již existujících překladů použijte `php artisan content:translate-existing --fresh`.

Strojový překlad je první redakční verze. Před významnou propagací je vhodné zejména u textů o lidech, asistenci a bezpečnosti projít angličtinu ručně.
