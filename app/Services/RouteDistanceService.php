<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Road distance between two places written as text (driving, in Bangladesh).
 *   - With GOOGLE_MAPS_API_KEY in .env: the Google Maps Routes API.
 *   - Without a key: OpenStreetMap (Nominatim finds the places, OSRM the road distance);
 *     free, no key. Nominatim allows one request a second, so lookups are spaced out.
 * Results are cached for 30 days so the same pair is not asked for again.
 */
class RouteDistanceService
{
    private const URL = 'https://routes.googleapis.com/directions/v2:computeRoutes';

    private const NOMINATIM = 'https://nominatim.openstreetmap.org/search';

    private const OSRM = 'https://router.project-osrm.org/route/v1/driving/';

    /** Generic client name (OpenStreetMap asks automated clients to name themselves) */
    private const USER_AGENT = 'InventoryApp/1.0 (vehicle distance)';

    /** Words people write => the word the map uses */
    private const SAME_AS = [
        "\u{098F}\u{09AF}\u{09BC}\u{09BE}\u{09B0}\u{09AA}\u{09CB}\u{09B0}\u{09CD}\u{099F}" => 'বিমানবন্দর', // এয়ারপোর্ট (য + nukta)
        "\u{098F}\u{09DF}\u{09BE}\u{09B0}\u{09AA}\u{09CB}\u{09B0}\u{09CD}\u{099F}" => 'বিমানবন্দর', // এয়ারপোর্ট (single য়)
        "\u{098F}\u{09AF}\u{09BC}\u{09BE}\u{09B0}\u{09AA}\u{09CB}\u{099F}" => 'বিমানবন্দর', // এয়ারপোট
        "\u{098F}\u{09DF}\u{09BE}\u{09B0}\u{09AA}\u{09CB}\u{099F}" => 'বিমানবন্দর',
        'airport' => 'বিমানবন্দর',
    ];

    /**
     * @return array{status: 'ok'|'not_found'|'error', meters?: int, source?: 'google'|'osm', from_name?: string, to_name?: string}
     */
    public function drivingDistance(string $from, string $to): array
    {
        $key = (string) config('services.google_maps.key');
        $source = $key !== '' ? 'google' : 'osm';

        $from = trim($from);
        $to = trim($to);
        $cacheKey = 'route-distance:' . $source . ':' . md5(mb_strtolower($from) . '|' . mb_strtolower($to));
        if (is_array($cached = Cache::get($cacheKey))) {
            return $cached + ['source' => $source];
        }

        $result = $source === 'google' ? $this->google($key, $from, $to) : $this->openStreetMap($from, $to);
        if ($result['status'] === 'ok') {
            Cache::put($cacheKey, $result, now()->addDays(30));
        }

        return $result + ['source' => $source];
    }

    private function google(string $key, string $from, string $to): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Goog-Api-Key' => $key,
                    'X-Goog-FieldMask' => 'routes.distanceMeters',
                ])
                ->post(self::URL, [
                    'origin' => ['address' => $from],
                    'destination' => ['address' => $to],
                    'travelMode' => 'DRIVE',
                    'regionCode' => 'BD',
                    'languageCode' => 'bn',
                ]);
        } catch (Throwable $e) {
            Log::warning('Route distance request failed', ['error' => $e->getMessage()]);

            return ['status' => 'error'];
        }

        if (! $response->successful()) {
            // The body may name the reason (e.g. API not enabled); the key itself is never logged
            Log::warning('Route distance request refused', ['status' => $response->status(), 'reason' => $response->json('error.status')]);

            return ['status' => in_array($response->status(), [400, 404], true) ? 'not_found' : 'error'];
        }

        $meters = $response->json('routes.0.distanceMeters');
        if (! is_numeric($meters) || $meters <= 0) {
            return ['status' => 'not_found'];
        }

        return ['status' => 'ok', 'meters' => (int) $meters];
    }

    private function openStreetMap(string $from, string $to): array
    {
        $start = $this->place($from);
        if ($start === 'error') {
            return ['status' => 'error'];
        }
        $end = $this->place($to);
        if ($end === 'error') {
            return ['status' => 'error'];
        }
        if (! $start || ! $end) {
            return ['status' => 'not_found'];
        }

        try {
            $response = Http::timeout(10)->withHeaders(['User-Agent' => self::USER_AGENT])
                ->get(self::OSRM . "{$start['lon']},{$start['lat']};{$end['lon']},{$end['lat']}", ['overview' => 'false']);
        } catch (Throwable $e) {
            Log::warning('OSRM request failed', ['error' => $e->getMessage()]);

            return ['status' => 'error'];
        }
        if (! $response->successful()) {
            Log::warning('OSRM request refused', ['status' => $response->status()]);

            return ['status' => $response->status() === 400 ? 'not_found' : 'error'];
        }
        $meters = $response->json('routes.0.distance');

        return is_numeric($meters) && $meters > 0
            ? ['status' => 'ok', 'meters' => (int) round($meters), 'from_name' => $start['name'], 'to_name' => $end['name']]
            : ['status' => 'not_found'];
    }

    /**
     * A place's position from Nominatim: ['lat', 'lon'], null when not found, 'error' when the
     * service did not answer. Searched in Dhaka first (where the office is), then anywhere in
     * Bangladesh. Cached for 30 days.
     */
    private function place(string $name): array|string|null
    {
        $cacheKey = 'osm-place:' . md5(mb_strtolower($name));
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Everyday words written the way the map names them (e.g. "এয়ারপোর্ট" is "বিমানবন্দর")
        $query = trim(preg_replace('/\s+/u', ' ', str_ireplace(array_keys(self::SAME_AS), array_values(self::SAME_AS), $name)));

        $place = null;
        foreach ([['viewbox' => '90.20,24.05,90.65,23.60', 'bounded' => 1], []] as $area) {
            $hit = $this->nominatim($query, $area);
            if ($hit === 'error') {
                return 'error';
            }
            if ($hit) {
                $place = $hit;
                break;
            }
        }

        Cache::put($cacheKey, $place, now()->addDays($place ? 30 : 1));

        return $place;
    }

    /** One Nominatim search, at most one request a second across all users (its usage policy). */
    private function nominatim(string $name, array $area): array|string|null
    {
        $lock = Cache::lock('osm-nominatim', 5);
        try {
            $lock->block(5);
            $last = (float) Cache::get('osm-nominatim-last', 0);
            $wait = 1.05 - (microtime(true) - $last);
            if ($wait > 0) {
                usleep((int) ($wait * 1000000));
            }
            $response = Http::timeout(10)->withHeaders(['User-Agent' => self::USER_AGENT])
                ->get(self::NOMINATIM, [
                    'q' => $name,
                    'format' => 'jsonv2',
                    'limit' => 1,
                    'countrycodes' => 'bd',
                    'accept-language' => 'bn,en',
                ] + $area);
            Cache::put('osm-nominatim-last', microtime(true), 60);
        } catch (Throwable $e) {
            Log::warning('Nominatim request failed', ['error' => $e->getMessage()]);

            return 'error';
        } finally {
            optional($lock)->release();
        }

        if (! $response->successful()) {
            Log::warning('Nominatim request refused', ['status' => $response->status()]);

            return 'error';
        }
        $hit = $response->json('0');

        if (! is_array($hit) || ! isset($hit['lat'], $hit['lon'])) {
            return null;
        }

        // A short name for the found place: its first three parts (e.g. place, area, city)
        $parts = array_slice(array_map('trim', explode(',', (string) ($hit['display_name'] ?? ''))), 0, 3);

        return ['lat' => (float) $hit['lat'], 'lon' => (float) $hit['lon'], 'name' => implode(', ', array_filter($parts))];
    }
}
