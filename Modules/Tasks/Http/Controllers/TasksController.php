<?php

namespace Modules\Tasks\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('tasks::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tasks::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date', // 'date' validation rule accepts datetime strings too usually
            'priority' => 'nullable|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $task = new \Modules\Tasks\Entities\Task();
        $task->title = $validated['title'];
        $task->description = $request->input('description', '');
        $task->due_date = $validated['due_date'];
        $task->priority = $request->input('priority', 'medium');
        $task->status = 'pending';
        // Assign to self if not specified, otherwise assign to selected user
        $task->assigned_to = $request->input('assigned_to', auth()->id());
        $task->assigned_by = auth()->id();
        $task->save();

        return redirect()->back()->with('success', 'Task created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('tasks::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('tasks::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Allow updating tasks assigned to me OR assigned by me (add assigned_by check if needed later, simplifying for now)
        $task = \Modules\Tasks\Entities\Task::findOrFail($id);

        // Simple authorization check
        if ($task->assigned_to != auth()->id() && $task->assigned_by != auth()->id()) {
            abort(403);
        }

        if ($request->has('status')) {
            $newStatus = $request->input('status');

            if ($newStatus == 'completed' && $task->status != 'completed') {
                $task->completed_at = now();
                $task->completed_by = auth()->id();
            } elseif ($newStatus != 'completed') {
                $task->completed_at = null;
                $task->completed_by = null;
            }

            $task->status = $newStatus;
        }

        if ($request->has('title')) {
            $task->title = $request->input('title');
        }

        // Handle other fields if necessary

        $task->save();

        return redirect()->back()->with('success', 'Task updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $task = \Modules\Tasks\Entities\Task::where('assigned_to', auth()->id())->findOrFail($id);
        $task->delete();

        return redirect()->back()->with('success', 'Task deleted.');
    }
}
