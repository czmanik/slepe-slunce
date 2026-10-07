<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PublishAndroidApp extends Command
{
    protected $signature = 'app:publish-android {apk : Cesta k sestavenému APK} {--version-code= : Android versionCode} {--version-name= : Zobrazená verze}';

    protected $description = 'Zveřejní ověřené APK a údaj o aktuální verzi na /app';

    public function handle(): int
    {
        $path = realpath($this->argument('apk'));
        $code = filter_var($this->option('version-code'), FILTER_VALIDATE_INT);
        $name = (string) $this->option('version-name');
        if (! $path || ! is_file($path) || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'apk') {
            $this->error('APK na zadané cestě neexistuje: '.$this->argument('apk'));
            $this->line('Nejprve zkopírujte skutečný .apk soubor na server a ověřte cestu příkazem ls -lh.');
            return self::FAILURE;
        }
        if ($code === false || $code < 1 || ! preg_match('/^[0-9]+(?:\.[0-9]+){1,3}$/', $name)) {
            $this->error('Zadejte kladný --version-code a --version-name (např. 0.2.0).');
            return self::FAILURE;
        }
        $previous = json_decode(Storage::disk('local')->get('android-release.json') ?: '{}', true);
        if (($previous['version_code'] ?? 0) >= $code) {
            $this->error('Nová verze musí mít vyšší versionCode než zveřejněná verze.');
            return self::FAILURE;
        }
        $file = "app/slepe-slunce-v{$code}.apk";
        $stream = fopen($path, 'rb');
        try {
            if (! Storage::disk('public')->put($file, $stream)) {
                $this->error('APK se nepodařilo uložit.');
                return self::FAILURE;
            }
        } finally {
            fclose($stream);
        }
        Storage::disk('local')->put('android-release.json', json_encode([
            'version_code' => $code, 'version_name' => $name, 'file' => $file, 'sha256' => hash_file('sha256', $path),
        ], JSON_THROW_ON_ERROR));
        $this->info("Verze {$name} byla zveřejněna na /app.");

        return self::SUCCESS;
    }
}
