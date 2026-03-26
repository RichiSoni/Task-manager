<?php
namespace App\Services;

use App\Models\Task;
use App\Events\TaskCreated;
use App\Jobs\ProcessTask;
use App\Notifications\TaskCreatedNotification;

class TaskService {
    public function create($user, array $data)
    {
        $task = $user->tasks()->create($data);

        ProcessTask::dispatch($task);

        event(new TaskCreated($task));

        $user->notify(new TaskCreatedNotification($task));

        return $task;
    }

    public function update(Task $task, array $data)
    {
        $task->update($data);
        return $task;
    }

    public function delete(Task $task)
    {
        return $task->delete();
    }
}