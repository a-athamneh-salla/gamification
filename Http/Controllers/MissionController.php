<?php

namespace Modules\Gamification\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Gamification\Contracts\MissionRepository;
use Modules\Gamification\Facades\Gamification;
use Modules\Gamification\Http\Resources\MissionResource;

class MissionController extends Controller
{
    /**
     * The mission repository instance.
     *
     * @var \Modules\Gamification\Contracts\MissionRepository
     */
    protected $missionRepository;

    /**
     * Create a new controller instance.
     *
     * @param \Modules\Gamification\Contracts\MissionRepository $missionRepository
     * @return void
     */
    public function __construct(MissionRepository $missionRepository)
    {
        $this->missionRepository = $missionRepository;
    }

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

    /**
     * Store a newly created mission (admin function).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:gamification_missions,key',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'total_points' => 'integer|min:0',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'sort_order' => 'integer|min:0',
            'tasks' => 'nullable|array',
            'tasks.*.task_id' => 'required|exists:gamification_tasks,id',
            'tasks.*.sort_order' => 'integer|min:0',
        ]);

        // Extract tasks data if present
        $tasksData = null;
        if (isset($validated['tasks'])) {
            $tasksData = $validated['tasks'];
            unset($validated['tasks']);
        }

        // Create the mission
        $mission = $this->missionRepository->create($validated);

        // Attach tasks if provided
        if ($tasksData) {
            $tasksSyncData = [];
            foreach ($tasksData as $task) {
                $tasksSyncData[$task['task_id']] = ['sort_order' => $task['sort_order'] ?? 0];
            }
            $mission->tasks()->sync($tasksSyncData);
        }

        return response()->json([
            'message' => 'Mission created successfully.',
            'data' => new MissionResource($mission),
        ], 201);
    }

    /**
     * Update the specified mission (admin function).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $mission = $this->missionRepository->find($id);
        
        if (!$mission) {
            return response()->json([
                'message' => 'Mission not found.',
            ], 404);
        }
        
        $validated = $request->validate([
            'key' => 'string|unique:gamification_missions,key,' . $id,
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'total_points' => 'integer|min:0',
            'is_active' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'sort_order' => 'integer|min:0',
            'tasks' => 'nullable|array',
            'tasks.*.task_id' => 'required|exists:gamification_tasks,id',
            'tasks.*.sort_order' => 'integer|min:0',
        ]);

        // Extract tasks data if present
        $tasksData = null;
        if (isset($validated['tasks'])) {
            $tasksData = $validated['tasks'];
            unset($validated['tasks']);
        }

        // Update the mission
        $mission = $this->missionRepository->update($mission, $validated);

        // Update tasks if provided
        if ($tasksData) {
            $tasksSyncData = [];
            foreach ($tasksData as $task) {
                $tasksSyncData[$task['task_id']] = ['sort_order' => $task['sort_order'] ?? 0];
            }
            $mission->tasks()->sync($tasksSyncData);
        }

        return response()->json([
            'message' => 'Mission updated successfully.',
            'data' => new MissionResource($mission),
        ]);
    }

    /**
     * Remove the specified mission (admin function).
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $mission = $this->missionRepository->find($id);
        
        if (!$mission) {
            return response()->json([
                'message' => 'Mission not found.',
            ], 404);
        }
        
        $this->missionRepository->delete($mission);
        
        return response()->json([
            'message' => 'Mission deleted successfully.',
        ]);
    }
}