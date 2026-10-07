<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AndroidAppController extends Controller
{
    public function index(): View
    {
        return view('app.index', ['release' => $this->release()]);
    }

    public function version(): JsonResponse
    {
        return response()->json($this->release() ?? ['version_code' => 0, 'version_name' => null, 'download_url' => null])
            ->header('Cache-Control', 'no-store');
    }

    private function release(): ?array
    {
        $manifest = Storage::disk('local')->get('android-release.json');
        if (! $manifest) return null;
        $release = json_decode($manifest, true);
        if (! is_array($release) || ! isset($release['version_code'], $release['version_name'], $release['file'])
            || ! preg_match('/^app\/slepe-slunce-v[0-9]+\.apk$/', $release['file'])
            || ! Storage::disk('public')->exists($release['file'])) return null;

        return [
            'version_code' => (int) $release['version_code'], 'version_name' => $release['version_name'],
            'download_url' => Storage::disk('public')->url($release['file']),
            'sha256' => $release['sha256'] ?? null,
        ];
    }
}
