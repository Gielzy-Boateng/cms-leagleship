<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SiteSettingController extends Controller
{
    /**
     * The live site settings: contact block, social links and newsletter copy.
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'data' => SiteSetting::current()->toApiResponse(),
        ]);
    }
}
