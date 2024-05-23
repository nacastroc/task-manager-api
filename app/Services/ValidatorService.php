<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Http\Request;


class ValidatorService
{
    /**
     * Validation rules for creating a new task.
     */
    const TASK_POST_VALIDATION = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'due_date' => 'required|date|after:today',
    ];

    /**
     * Validation rules for registering a new user.
     */
    const USER_REGISTER_VALIDATION = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => [
            'required',
            'string',
            'min:8',              // must be at least 8 characters in length
            'regex:/[a-z]/',      // must contain at least one lowercase letter
            'regex:/[A-Z]/',      // must contain at least one uppercase letter
            'regex:/[0-9]/',      // must contain at least one digit
            'regex:/[@$!%*#?&]/', // must contain a special character
        ]
    ];

    /**
     * Validation rules for logging in a user.
     */
    const USER_LOGIN_VALIDATION = [
        'email' => 'required|string|email|max:255',
        'password' => [
            'required',
            'string',
        ]
    ];

    /**
     * Validation rules for filtering a list.
     */
    const LIST_FILTER_VALIDATION = [
        'filter' => 'string|regex:/^\[(([a-z_][a-z0-9_]*)=([^,]*),?)+\]$/',
    ];

    /**
     * Validates the request data for creating a new task.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function validateTaskPost($request)
    {
        return $request->validate(self::TASK_POST_VALIDATION);
    }

    /**
     * Validates the request data for updating a new task.
     *
     * @param \Illuminate\Http\Request $request
     * @return Task
     */
    public function validateTaskPut($request)
    {
        $user = $request->user();
        $id = $request->id;

        $item = Task::find($id);
        if (!$item)
            return response()->json(['message' => "Object of tasks with id {$id} not found"], 404);
        if ($item->user_id !== $user->id && !$user->admin)
            return response()->json(['message' => config('constants.messages.http_403')], 403);

        $validData = $request->validate(self::TASK_POST_VALIDATION);

        $item->title = $validData['title'];
        $item->description = $validData['description'];
        $item->due_date = $validData['due_date'];

        return $item;
    }

    /**
     * Validates the request data for registering a new user.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function validateUserRegister(Request $request)
    {
        return $request->validate(self::USER_REGISTER_VALIDATION);
    }

    /**
     * Validates the request data for logging in a user.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function validateUserLogin(Request $request)
    {
        return $request->validate(self::USER_LOGIN_VALIDATION);
    }

    /**
     * Validates the request data for updating a user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|array
     */
    public function validateUserUpdate($request)
    {
        $user = $request->user();
        $id = $request->id;
        if (!$user->admin && $user->id !== $id)
            return response()->json(['message' => config('constants.messages.http_403')], 403);
        return $request->validate(self::USER_REGISTER_VALIDATION);
    }

    /**
     * Validates the request data for filtering a list.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function validateListFilter($request)
    {
        return $request->validate(self::LIST_FILTER_VALIDATION);
    }
}
