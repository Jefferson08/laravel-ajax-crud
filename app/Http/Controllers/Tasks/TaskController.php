<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tasks = Task::select(['id', 'title', 'body', 'created_at', 'updated_at']);

        return DataTables::of($tasks)
        ->addColumn('action', 'actions')
        ->editColumn('created_at', function ($task) {
            return $task->created_at->format('d/m/Y H:i:s');
        })
        ->editColumn('updated_at', function ($task) {
            return $task->updated_at->format('d/m/Y H:i:s');
        })
        ->make(true);
    }

    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_title' => 'required|string|max:50',
            'task_body' => 'required|string|max:255'
        ]);

        if($validator->fails()){
            $errors = $validator->errors();

            return response([
                'success' => false,
                'message' => 'Error, check data!!!',
                'errors' => $errors
            ]);
        }

        $task = new Task();        
        $task->title = $request->task_title;
        $task->body = $request->task_body;
        $task->save();

        return response([
            'success' => true,
            'message' => 'User created successfully!!'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {

        if($task){
            return $task;
        }

        return response(['error' => 'User not found'], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        
        $validator = Validator::make($request->all(), [
            'task_title' => 'required|string|max:50',
            'task_body' => 'required|string|max:255'
        ]);

        if($validator->fails()){
            $errors = $validator->errors();

            return response([
                'success' => false,
                'message' => 'Error, check the data!',
                'errors' => $errors
            ]);
        }

        $task->title = $request->task_title;
        $task->body = $request->task_body;
        $task->update();

        return response([
            'success' => true,
            'message' => 'Task edited successfully!!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return response([
            'success' => true,
            'message' => 'Task deleted successfully!!'
        ]);
    }
}
