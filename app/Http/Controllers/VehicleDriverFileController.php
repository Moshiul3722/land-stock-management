<?php

namespace App\Http\Controllers;

use App\Models\VehicleDriver;
use Illuminate\Support\Facades\Storage;

/**
 * Shows a driver's photo or licence image. The files are stored outside the public folder,
 * so only users allowed to open the driver or vehicle pages can see them (see the
 * `permission` middleware and config/menu_access.php).
 */
class VehicleDriverFileController extends Controller
{
    public function show(VehicleDriver $driver, string $kind)
    {
        $path = $kind === 'photo' ? $driver->photo_path : $driver->license_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
