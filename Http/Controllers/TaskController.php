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

    /**
     * Display the specified task
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        $task = $this->taskRepository->find($id);
        
        if (!$task) {
            return response()->json([
                'message' => 'Task not found.',
            ], 404);
        }
        
        return response()->json([
            'data' => new TaskResource($task),
        ]);
    }

    /**
     * Get a list of available event types for tasks
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEventTypes(): JsonResponse
    {
        $eventTypes = [
            ['name' => 'Order Completed', 'id' => 1],
            ['name' => 'Order Canceled', 'id' => 2],
            ['name' => 'Order Refunded', 'id' => 3],
            ['name' => 'Product Purchased', 'id' => 4],
            ['name' => 'Product Reviewed', 'id' => 5],
            ['name' => 'Product Shared', 'id' => 6],
            ['name' => 'Product Added to Cart', 'id' => 7],
            ['name' => 'Product Removed from Cart', 'id' => 8],
            ['name' => 'Product Wishlisted', 'id' => 9],
            ['name' => 'Product Unwishlisted', 'id' => 10],
            ['name' => 'Product Comparison Started', 'id' => 11],
            ['name' => 'Product Comparison Completed', 'id' => 12],
            ['name' => 'Product Comparison Canceled', 'id' => 13],
            ['name' => 'Checkout Completed', 'id' => 14],
            ['name' => 'Checkout Started', 'id' => 15],
            ['name' => 'Product Added', 'id' => 16],
            ['name' => 'Product Viewed', 'id' => 17],
            ['name' => 'Product List Viewed', 'id' => 18],
            ['name' => 'Payment Info Entered', 'id' => 19],
            ['name' => 'User Registered', 'id' => 20],
            ['name' => 'Products Searched', 'id' => 21],
            ['name' => 'page', 'id' => 22],
            ['name' => 'User Login', 'id' => 23],
            ['name' => 'Product Added to Wishlist', 'id' => 24],
            ['name' => 'Product Removed from Wishlist', 'id' => 25],
            ['name' => 'Product Added to Cart', 'id' => 26],
            ['name' => 'Product Removed from Cart', 'id' => 27],
            ['name' => 'Product Added to Comparison', 'id' => 28],
            ['name' => 'Product Removed from Comparison', 'id' => 29],
            ['name' => 'Product Viewed in Comparison', 'id' => 30],
            ['name' => 'Product List Viewed in Comparison', 'id' => 31],
            ['name' => 'Product Comparison Started', 'id' => 32],
            ['name' => 'Product Comparison Completed', 'id' => 33],
            ['name' => 'Product Comparison Canceled', 'id' => 34],
            ['name' => 'Checkout Started', 'id' => 35],
            ['name' => 'Checkout Completed', 'id' => 36],
            ['name' => 'Payment Info Entered', 'id' => 37],
            ['name' => 'User Registered', 'id' => 38],
            ['name' => 'User Login', 'id' => 39],
            ['name' => 'User Logout', 'id' => 40],
            ['name' => 'User Profile Updated', 'id' => 41],
            ['name' => 'User Password Changed', 'id' => 42],
            ['name' => 'User Account Deleted', 'id' => 43],
            ['name' => 'User Account Reactivated', 'id' => 44],
            ['name' => 'User Account Deactivated', 'id' => 45],
            ['name' => 'User Account Suspended', 'id' => 46],
            ['name' => 'User Account Unsuspended', 'id' => 47],
            ['name' => 'User Account Locked', 'id' => 48],
            ['name' => 'User Account Unlocked', 'id' => 49],
            ['name' => 'User Account Verified', 'id' => 50],
            ['name' => 'User Account Unverified', 'id' => 51],
            ['name' => 'User Account Email Changed', 'id' => 52],
            ['name' => 'User Account Phone Number Changed', 'id' => 53],
            ['name' => 'User Account Address Changed', 'id' => 54],
            ['name' => 'User Account Preferences Changed', 'id' => 55],
            ['name' => 'User Account Notifications Changed', 'id' => 56],
            ['name' => 'User Account Subscriptions Changed', 'id' => 57],
            ['name' => 'User Account Payment Methods Changed', 'id' => 58],
            ['name' => 'User Account Billing Address Changed', 'id' => 59],
            ['name' => 'User Account Shipping Address Changed', 'id' => 60],
            ['name' => 'User Account Payment History Changed', 'id' => 61],
            ['name' => 'User Account Order History Changed', 'id' => 62],
            ['name' => 'User Account Wishlist Changed', 'id' => 63],
            ['name' => 'User Account Comparison List Changed', 'id' => 64],
            ['name' => 'User Account Cart Changed', 'id' => 65],
            ['name' => 'User Account Preferences Reset', 'id' => 66],
            ['name' => 'User Account Notifications Reset', 'id' => 67],
            ['name' => 'User Account Subscriptions Reset', 'id' => 68],
            ['name' => 'User Account Payment Methods Reset', 'id' => 69],
            ['name' => 'User Account Billing Address Reset', 'id' => 70],
            ['name' => 'User Account Shipping Address Reset', 'id' => 71],
            ['name' => 'User Account Payment History Reset', 'id' => 72],
            ['name' => 'User Account Order History Reset', 'id' => 73],
            ['name' => 'User Account Wishlist Reset', 'id' => 74],
            ['name' => 'User Account Comparison List Reset', 'id' => 75],
            ['name' => 'User Account Cart Reset', 'id' => 76],
            ['name' => 'User Account Preferences Cleared', 'id' => 77],
            ['name' => 'User Account Notifications Cleared', 'id' => 78],
            ['name' => 'User Account Subscriptions Cleared', 'id' => 79],
            ['name' => 'User Account Payment Methods Cleared', 'id' => 80],
            ['name' => 'User Account Billing Address Cleared', 'id' => 81],
            ['name' => 'User Account Shipping Address Cleared', 'id' => 82],
            ['name' => 'User Account Payment History Cleared', 'id' => 83],
            ['name' => 'User Account Order History Cleared', 'id' => 84],
            ['name' => 'User Account Wishlist Cleared', 'id' => 85],
            ['name' => 'User Account Comparison List Cleared', 'id' => 86],
            ['name' => 'User Account Cart Cleared', 'id' => 87],
            ['name' => 'User Account Preferences Deleted', 'id' => 88],
            ['name' => 'User Account Notifications Deleted', 'id' => 89],
            ['name' => 'User Account Subscriptions Deleted', 'id' => 90],
            ['name' => 'User Account Payment Methods Deleted', 'id' => 91],
            ['name' => 'User Account Billing Address Deleted', 'id' => 92],
            ['name' => 'User Account Shipping Address Deleted', 'id' => 93],
            ['name' => 'User Account Payment History Deleted', 'id' => 94],
            ['name' => 'User Account Order History Deleted', 'id' => 95],
            ['name' => 'User Account Wishlist Deleted', 'id' => 96],
            ['name' => 'User Account Comparison List Deleted', 'id' => 97],
            ['name' => 'User Account Cart Deleted', 'id' => 98],
            ['name' => 'User Account Preferences Archived', 'id' => 99],
            ['name' => 'User Account Notifications Archived', 'id' => 100],
            ['name' => 'User Account Subscriptions Archived', 'id' => 101],
            ['name' => 'User Account Payment Methods Archived', 'id' => 102],
            ['name' => 'User Account Billing Address Archived', 'id' => 103],
            ['name' => 'User Account Shipping Address Archived', 'id' => 104],
            ['name' => 'User Account Payment History Archived', 'id' => 105],
            ['name' => 'User Account Order History Archived', 'id' => 106],
            ['name' => 'User Account Wishlist Archived', 'id' => 107],
            ['name' => 'User Account Comparison List Archived', 'id' => 108],
            ['name' => 'User Account Cart Archived', 'id' => 109],
            ['name' => 'User Account Preferences Restored', 'id' => 110],
            ['name' => 'User Account Notifications Restored', 'id' => 111],
            ['name' => 'User Account Subscriptions Restored', 'id' => 112],
            ['name' => 'User Account Payment Methods Restored', 'id' => 113],
            ['name' => 'User Account Billing Address Restored', 'id' => 114],
            ['name' => 'User Account Shipping Address Restored', 'id' => 115],
            ['name' => 'User Account Payment History Restored', 'id' => 116],
            ['name' => 'User Account Order History Restored', 'id' => 117],
            ['name' => 'User Account Wishlist Restored', 'id' => 118],
            ['name' => 'User Account Comparison List Restored', 'id' => 119],
            ['name' => 'User Account Cart Restored', 'id' => 120],
        ];
        
        return response()->json([
            'data' => (array) $eventTypes,
            'meta' => [
                'total' => count($eventTypes),
            ],
        ]);
    }
}