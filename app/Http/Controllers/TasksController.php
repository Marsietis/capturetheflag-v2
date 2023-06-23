<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TasksController extends Controller
{
    public function index(): View
    {
        return view('welcome');
    }

    public function store(Request $request): RedirectResponse
    {
        //files
        $task = new Task;
        $task->title = $request->input('title');
        $task->description = $request->input('description');
        $task->points = $request->input('points');
        $task->link = $request->input('link');
        $task->flag = $request->input('flag');
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('public');
            $task->file = $path;
        }
        $task->save();
        return redirect('/dashboard');
    }

    public function show($id): View
    {
        $task = Task::findOrFail($id);
        $user = auth()->user();
        $completedTasks = $user->tasks_completed;
        $completed = str_contains($completedTasks, $id);
        return view('task', compact('task', 'completed'));
    }

    public function check(Request $request): RedirectResponse
    {
        $inputtedFlag = $request->input('flag');
        $taskId = $request->input('task_id');
        $task = Task::findOrFail($taskId);
        $taskPoints = $task->points;

        $user = auth()->user();
        $completedTasks = $user->tasks_completed;

        if (str_contains($completedTasks, $taskId)) {
            return redirect()->back()->with('error', 'You have already completed this task . ');
        }

        if ($task->flag === $inputtedFlag) {
            $user->score += $taskPoints;
            $user->tasks_completed = $completedTasks . $taskId . ',';
            $user->save();
            return redirect('dashboard')->with('success', 'Task completed successfully!');
        } else {
            return redirect()->back()->with('error', 'Sorry, that is not the correct flag . Please try again . ');
        }
    }

}
