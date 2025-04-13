<?php

namespace Modules\Gamification\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Gamification\Contracts\RuleRepository;
use Modules\Gamification\Models\Rule;
use Illuminate\Validation\Rule as ValidationRule;

class RuleController extends Controller
{
    /**
     * The rule repository instance.
     *
     * @var \Modules\Gamification\Contracts\RuleRepository
     */
    protected $ruleRepository;

    /**
     * Create a new controller instance.
     *
     * @param \Modules\Gamification\Contracts\RuleRepository $ruleRepository
     * @return void
     */
    public function __construct(RuleRepository $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * Display a listing of rules.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $missionId = $request->input('mission_id');
        
        if ($missionId) {
            $rules = $this->ruleRepository->getAllForMission($missionId);
        } else {
            $rules = Rule::all();
        }
        
        return response()->json([
            'data' => $rules,
            'meta' => [
                'total' => $rules->count(),
            ],
        ]);
    }

    /**
     * Store a newly created rule.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mission_id' => 'required|exists:gamification_missions,id',
            'rule_type' => 'required|string|in:start,finish',
            'condition_type' => 'required|string|in:mission_completion,tasks_completion,date_range,custom',
            'condition_payload' => 'required|json',
        ]);
        
        $rule = $this->ruleRepository->create($validated);
        
        return response()->json([
            'message' => 'Rule created successfully.',
            'data' => $rule,
        ], 201);
    }

    /**
     * Display the specified rule.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $rule = $this->ruleRepository->find($id);
        
        if (!$rule) {
            return response()->json([
                'message' => 'Rule not found.',
            ], 404);
        }
        
        return response()->json([
            'data' => $rule,
        ]);
    }

    /**
     * Update the specified rule.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $rule = $this->ruleRepository->find($id);
        
        if (!$rule) {
            return response()->json([
                'message' => 'Rule not found.',
            ], 404);
        }
        
        $validated = $request->validate([
            'mission_id' => 'exists:gamification_missions,id',
            'rule_type' => 'string|in:start,finish',
            'condition_type' => 'string|in:mission_completion,tasks_completion,date_range,custom',
            'condition_payload' => 'json',
        ]);
        
        $rule = $this->ruleRepository->update($rule, $validated);
        
        return response()->json([
            'message' => 'Rule updated successfully.',
            'data' => $rule,
        ]);
    }

    /**
     * Remove the specified rule.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $rule = $this->ruleRepository->find($id);
        
        if (!$rule) {
            return response()->json([
                'message' => 'Rule not found.',
            ], 404);
        }
        
        $this->ruleRepository->delete($rule);
        
        return response()->json([
            'message' => 'Rule deleted successfully.',
        ]);
    }
}