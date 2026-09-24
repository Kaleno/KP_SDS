<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class PwaServiceWorkerController extends Controller
{
    public function __invoke(): Response
    {
        $path = resource_path('pwa/sw.js');

        abort_unless(is_file($path), 404);

        return response((string) file_get_contents($path), 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Service-Worker-Allowed' => '/',
        ]);
    }
}
