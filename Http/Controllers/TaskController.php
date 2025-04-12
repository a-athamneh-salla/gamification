<?php

namespace Modules\Gamification\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Gamification\Contracts\TaskRepository;
use Modules\Gamification\Models\Task;
use Modules\Gamification\Models\TaskCompletion;
use Modules\Gamification\Http\Resources\TaskResource;

class TaskController extends Controller
{
    /**
     * The task repository instance.
     *
     * @var \Modules\Gamification\Contracts\TaskRepository
     */
    protected $taskRepository;

    /**
     * Create a new controller instance.
     *
     * @param \Modules\Gamification\Contracts\TaskRepository $taskRepository
     * @return void
     */
    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    /**
     * Display a listing of the active tasks.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $tasks = $this->taskRepository->all(['is_active' => true]);
        
        return response()->json([
            'data' => TaskResource::collection($tasks),
            'meta' => [
                'total' => $tasks->count(),
            ],
        ]);
    }

    /**
     * Manually complete a task (admin function)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeManually(Request $request, $id): JsonResponse
    {
        $storeId = $request->user()->id;
        $missionId = $request->input('mission_id');
        
        if (!$missionId) {
            return response()->json([
                'message' => 'Mission ID is required.',
            ], 422);
        }
        
        $task = $this->taskRepository->find($id);
        
        if (!$task) {
            return response()->json([
                'message' => 'Task not found.',
            ], 404);
        }
        
        // Check if the task belongs to the mission
        if (!$task->missions->contains($missionId)) {
            return response()->json([
                'message' => 'The specified task does not belong to the provided mission.',
            ], 422);
        }
        
        // Find or create the task completion record
        $taskCompletion = TaskCompletion::firstOrCreate([
            'store_id' => $storeId,
            'task_id' => $task->id,
            'mission_id' => $missionId,
        ], [
            'status' => 'not_started',
        ]);
        
        // Mark as completed if not already
        if (!$taskCompletion->isCompleted()) {
            $taskCompletion->markAsCompleted();
            
            return response()->json([
                'message' => 'Task completed successfully.',
                'data' => [
                    'task_id' => $task->id,
                    'task_key' => $task->key,
                    'task_name' => $task->name,
                    'mission_id' => $missionId,
                ],
            ]);
        }
        
        return response()->json([
            'message' => 'Task was already completed.',
            'data' => [
                'task_id' => $task->id,
                'task_key' => $task->key,
                'task_name' => $task->name,
                'mission_id' => $missionId,
                'completed_at' => $taskCompletion->completed_at->toIso8601String(),
            ],
        ]);
    }

    /**
     * Store a newly created task (admin function)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:gamification_tasks,key',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'points' => 'integer|min:0',
            'icon' => 'nullable|string',
            'event_name' => 'required|string',
            'event_payload_conditions' => 'nullable|json',
            'is_active' => 'boolean',
        ]);
        
        $task = $this->taskRepository->create($validated);
        
        return response()->json([
            'message' => 'Task created successfully.',
            'data' => new TaskResource($task),
        ], 201);
    }

    /**
     * Update the specified task (admin function)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $task = $this->taskRepository->find($id);
        
        if (!$task) {
            return response()->json([
                'message' => 'Task not found.',
            ], 404);
        }
        
        $validated = $request->validate([
            'key' => 'string|unique:gamification_tasks,key,' . $id,
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'points' => 'integer|min:0',
            'icon' => 'nullable|string',
            'event_name' => 'string',
            'event_payload_conditions' => 'nullable|json',
            'is_active' => 'boolean',
        ]);
        
        $task = $this->taskRepository->update($task, $validated);
        
        return response()->json([
            'message' => 'Task updated successfully.',
            'data' => new TaskResource($task),
        ]);
    }

    /**
     * Remove the specified task (admin function)
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $task = $this->taskRepository->find($id);
        
        if (!$task) {
            return response()->json([
                'message' => 'Task not found.',
            ], 404);
        }
        
        $this->taskRepository->delete($task);
        
        return response()->json([
            'message' => 'Task deleted successfully.',
        ]);
    }
}