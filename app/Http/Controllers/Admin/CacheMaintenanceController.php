<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CacheMaintenanceController extends Controller
{
    /**
     * Temporarily clear the application cache when a valid token is provided.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function clear(Request $request)
    {
        $token = (string) config('services.cache_clear.token');

        if (empty($token)) {
            Log::warning('Cache clear attempted without configured token.');

            return response()->json([
                'status' => 'error',
                'message' => 'Cache clear token is not configured.',
            ], 503);
        }

        if (!hash_equals($token, (string) $request->query('token'))) {
            Log::warning('Cache clear denied due to invalid token.', [
                'ip' => $request->ip(),
            ]);

            abort(403);
        }

        Artisan::call('cache:clear');

        Log::info('Application cache cleared via temporary maintenance route.', [
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Cache cleared successfully.',
        ]);
    }
}

