<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Task::query()->with('user')->where('user_id', $request->user()->id);

        //Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        //Filter by date range
        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        $tasks = $query->latest()->paginate(10);
        return response()->json(['data' => TaskResource::collection($tasks), 'message' => 'Data Fetched Successfully'], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        //$this->authorize('create', $request->user());

        $task = $this->taskService->create($request->user(), $request->validated());

        return response()->json(['data' => new TaskResource($task->load('user')), 'message' => 'Data Stored Successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = Task::find($id);
        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        $this->authorize('view', $task);

        return response()->json(['data' => new TaskResource($task->load('user')), 'message' => 'Data Fetched Successfully'], 200);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task);
        $this->taskService->update($task, $request->validated());
        return response()->json(['data' => new TaskResource($task), 'message' => 'Data Updated Successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $this->taskService->delete($task);
        return response()->json(['message' => "Task deleted successfully"]);
    }
}
