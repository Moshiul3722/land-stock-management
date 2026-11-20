<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Road distance between two places written as text, from the Google Maps Routes API
 * (driving, in Bangladesh). The key comes from GOOGLE_MAPS_API_KEY in .env; results are
 * cached for 30 days so the same pair is not asked for again.
 */
class RouteDistanceService
{
    private const URL = 'https://routes.googleapis.com/directions/v2:computeRoutes';

    /**
     * @return array{status: 'ok'|'no_key'|'not_found'|'error', meters?: int}
     */
    public function drivingDistance(string $from, string $to): array
    {
        $key = (string) config('services.google_maps.key');
        if ($key === '') {
            return ['status' => 'no_key'];
        }

        $from = trim($from);
        $to = trim($to);
        $cacheKey = 'route-distance:' . md5(mb_strtolower($from) . '|' . mb_strtolower($to));
        if (($meters = Cache::get($cacheKey)) !== null) {
            return ['status' => 'ok', 'meters' => (int) $meters];
        }

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

        Cache::put($cacheKey, (int) $meters, now()->addDays(30));

        return ['status' => 'ok', 'meters' => (int) $meters];
    }
}
