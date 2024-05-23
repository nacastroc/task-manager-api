<?php

namespace App\Http\Middleware\Api;

use App\Services\QueryService;
use App\Models\User;
use App\Models\Task;
use Closure;
use Illuminate\Http\Request;

/**
 * Middleware to handle security by authorization
 * on generic API routes.
 */
class RouteSecurity
{
    protected $queryService;

    public function __construct(QueryService $queryService)
    {
        $this->queryService = $queryService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $method = $request->method();
        $model = $request->input('data-model');

        switch ($method) {
            case 'POST':
                // TODO
                return $next($request);
            case 'PUT':
                // TODO
                return $next($request);
            case 'DELETE':
                return $this->_delete($user, $model, $request, $next);
            case 'GET':
                $id = $request->route('id');
                if ($id)
                    return $this->show($id, $user, $model, $request, $next);
                else
                    return $this->list($user, $model, $request, $next);
            default:
                return response()->json(['message' => config('constants.messages.http_405')], 405);
        }
    }

    private function _delete($user, $model, $request, $next)
    {
        $ids = explode(',', $request->ids);

        // Delete users
        if ($model instanceof User) {
            if (!$user->admin)
                // Only admins allowed
                return response()->json(['message' => config('constants.messages.http_403')], 403);
            if (in_array($user->id, $ids))
                // No self-delete
                return response()->json(['message' => config('constants.messages.http_403_self_delete')], 403);
        }

        if ($model instanceof Task) {
            // Retrieve the tasks to be deleted
            $tasks = Task::whereIn('id', $ids)->get();

            // Check if the user is the owner or an admin
            foreach ($tasks as $task) {
                if ($task->user_id !== $user->id && !$user->admin) {
                    return response()->json(['message' => config('constants.messages.http_403')], 403);
                }
            }
        }

        return $next($request);
    }

    private function list($user, $model, $request, $next) {
        if ($model instanceof User) {
            if (!$user->admin)
                // Only admins allowed
                return response()->json(['message' => config('constants.messages.http_403')], 403);
        }

        return $next($request);
    }

    private function show($id, $user, $model, $request, $next) {
        if ($model instanceof User && !$user->admin && $user->id !== $id) {
            return response()->json(['message' => config('constants.messages.http_403')], 403);
        }

        return $next($request);
    }
}
