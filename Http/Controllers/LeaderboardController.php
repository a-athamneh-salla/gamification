<?php

namespace Salla\Gamification\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Salla\Gamification\Contracts\TaskRepository;

class LeaderboardController extends Controller
{
    /**
     * Display the merchant leaderboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        
        // Cache the leaderboard data for performance
        $cacheTtl = config('gamification.cache.ttl', 3600);
        $cacheKey = config('gamification.cache.prefix', 'gamification_') . 'leaderboard_' . $limit;
        
        $leaderboard = Cache::remember($cacheKey, $cacheTtl, function () use ($limit) {
            $storeModel = config('gamification.store_model');
            
            // If level-up package is enabled, use its points system
            if (config('gamification.level_up.enabled')) {
                // Get top stores by experience points
                return $storeModel::query()
                    ->with('experience')
                    ->whereHas('experience')
                    ->orderByDesc('experience.points')
                    ->limit($limit)
                    ->get()
                    ->map(function ($store) {
                        return [
                            'store_id' => $store->id,
                            'store_name' => $store->name ?? 'Store #' . $store->id,
                            'points' => $store->experience->points ?? 0,
                            'level' => $store->getLevel() ?? 1,
                        ];
                    });
            } else {
                // Fallback implementation using task completions
                $stores = $storeModel::withCount(['taskCompletions as points' => function ($query) {
                    $query->select(\DB::raw('SUM(gamification_tasks.points)'))
                        ->join('gamification_tasks', 'gamification_task_completion.task_id', '=', 'gamification_tasks.id')
                        ->where('gamification_task_completion.status', 'completed');
                }])
                ->orderByDesc('points')
                ->limit($limit)
                ->get();
                
                return $stores->map(function ($store) {
                    return [
                        'store_id' => $store->id,
                        'store_name' => $store->name ?? 'Store #' . $store->id,
                        'points' => $store->points ?? 0,
                        'level' => 1, // Default level if level-up integration is disabled
                    ];
                });
            }
        });
        
        // Add ranking to each store
        $leaderboard = collect($leaderboard)->values()->map(function ($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });
        
        return response()->json([
            'data' => $leaderboard,
            'meta' => [
                'total' => $leaderboard->count(),
            ],
        ]);
    }
}