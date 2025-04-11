<?php

namespace Salla\Gamification\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Salla\Gamification\Facades\Gamification;
use Salla\Gamification\Http\Resources\MissionResource;

class MissionController extends Controller
{
    /**
     * Display a listing of available missions for the authenticated store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $storeId = $request->user()->id;
        $includeWithTasks = $request->boolean('include_tasks', false);
        
        $missions = $includeWithTasks
            ? Gamification::getMissionsWithTasks($storeId)
            : Gamification::getAvailableMissions($storeId);
            
        return response()->json([
            'data' => MissionResource::collection($missions),
            'meta' => [
                'total' => $missions->count(),
            ],
        ]);
    }

    /**
     * Display the specified mission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id): JsonResponse
    {
        $storeId = $request->user()->id;
        $missions = Gamification::getMissionsWithTasks($storeId);
        
        $mission = $missions->firstWhere('id', $id);
        
        if (!$mission) {
            return response()->json([
                'message' => 'Mission not found or not available for this store.',
            ], 404);
        }
        
        return response()->json([
            'data' => new MissionResource($mission),
        ]);
    }

    /**
     * Ignore a mission.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function ignore(Request $request, $id): JsonResponse
    {
        $storeId = $request->user()->id;
        $ignored = Gamification::ignoreMission($id, $storeId);
        
        if (!$ignored) {
            return response()->json([
                'message' => 'Unable to ignore the mission. The mission might not exist or is not available for this store.',
            ], 404);
        }
        
        return response()->json([
            'message' => 'Mission successfully ignored.',
        ]);
    }
}