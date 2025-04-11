<?php

namespace Salla\Gamification\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Salla\Gamification\Facades\Gamification;

class ProgressController extends Controller
{
    /**
     * Get a summary of the store's gamification progress.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary(Request $request): JsonResponse
    {
        $storeId = $request->user()->id;
        $summary = Gamification::getProgressSummary($storeId);
        
        return response()->json([
            'data' => $summary
        ]);
    }
}