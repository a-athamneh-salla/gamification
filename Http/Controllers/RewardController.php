<?php

namespace Salla\Gamification\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Salla\Gamification\Contracts\RewardRepository;
use Salla\Gamification\Facades\Gamification;
use Salla\Gamification\Http\Resources\RewardResource;

class RewardController extends Controller
{
    /**
     * The reward repository instance.
     *
     * @var \Salla\Gamification\Contracts\RewardRepository
     */
    protected $rewardRepository;

    /**
     * Create a new controller instance.
     *
     * @param \Salla\Gamification\Contracts\RewardRepository $rewardRepository
     * @return void
     */
    public function __construct(RewardRepository $rewardRepository)
    {
        $this->rewardRepository = $rewardRepository;
    }

    /**
     * Display a listing of rewards earned by the authenticated store.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $storeId = $request->user()->id;
        $rewards = Gamification::getStoreRewards($storeId);
        
        // Eager load mission relationship if it's not already loaded
        if (!$rewards->first() || !$rewards->first()->relationLoaded('mission')) {
            $rewards->load('mission');
        }
        
        return response()->json([
            'data' => RewardResource::collection($rewards),
            'meta' => [
                'total' => $rewards->count(),
            ],
        ]);
    }

    /**
     * Store a newly created reward (admin function).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mission_id' => 'required|exists:gamification_missions,id',
            'reward_type' => 'required|string|in:points,badge,coupon,feature_unlock',
            'reward_value' => 'required|string',
            'reward_meta' => 'nullable|json',
        ]);
        
        $reward = $this->rewardRepository->create($validated);
        
        return response()->json([
            'message' => 'Reward created successfully.',
            'data' => new RewardResource($reward),
        ], 201);
    }

    /**
     * Update the specified reward (admin function).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $reward = $this->rewardRepository->find($id);
        
        if (!$reward) {
            return response()->json([
                'message' => 'Reward not found.',
            ], 404);
        }
        
        $validated = $request->validate([
            'mission_id' => 'exists:gamification_missions,id',
            'reward_type' => 'string|in:points,badge,coupon,feature_unlock',
            'reward_value' => 'string',
            'reward_meta' => 'nullable|json',
        ]);
        
        $reward = $this->rewardRepository->update($reward, $validated);
        
        return response()->json([
            'message' => 'Reward updated successfully.',
            'data' => new RewardResource($reward),
        ]);
    }

    /**
     * Remove the specified reward (admin function).
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $reward = $this->rewardRepository->find($id);
        
        if (!$reward) {
            return response()->json([
                'message' => 'Reward not found.',
            ], 404);
        }
        
        $this->rewardRepository->delete($reward);
        
        return response()->json([
            'message' => 'Reward deleted successfully.',
        ]);
    }
}