<?php

namespace Modules\Gamification\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Gamification\Models\Badge;
use Modules\Gamification\Models\StoreBadge;
use Modules\Gamification\Http\Resources\BadgeResource;

class BadgeController extends Controller
{
    /**
     * Display a listing of badges earned by the authenticated store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $storeId = $request->user()->id;
        
        // Get all earned badges for the store
        $badges = Badge::with(['storeBadges' => function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        }])
        ->whereHas('storeBadges', function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        })
        ->get();
        
        // Format the badges for the response
        $formattedBadges = $badges->map(function ($badge) use ($storeId) {
            $storeBadge = $badge->storeBadges->where('store_id', $storeId)->first();
            $badge->pivot = $storeBadge;
            return $badge;
        });
        
        return response()->json([
            'data' => BadgeResource::collection($formattedBadges),
            'meta' => [
                'total' => $badges->count(),
            ],
        ]);
    }
}