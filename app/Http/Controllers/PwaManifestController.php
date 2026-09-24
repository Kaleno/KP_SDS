<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class PwaManifestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $name = (string) config('app.name');

        return response()->json([
            'id' => '/',
            'name' => $name,
            'short_name' => 'KP SDS',
            'description' => 'Absensi, setoran, dan kas Taman Pendidikan Al-Qur\'an.',
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => '#f7f4ee',
            'theme_color' => '#0e221f',
            'lang' => str_replace('_', '-', app()->getLocale()),
            'icons' => [
                [
                    'src' => '/icons/icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/icons/icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/icons/icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ], 200, [
            'Content-Type' => 'application/manifest+json',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
