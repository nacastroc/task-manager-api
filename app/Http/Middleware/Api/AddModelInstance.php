<?php

namespace App\Http\Middleware\Api;

use App\Services\QueryService;
use Closure;
use Illuminate\Http\Request;

/**
 * Middleware to add model class instance to request data.
 *
 * This middleware fetches an instance of a model class based on the route parameter
 * and adds it to the request data for further processing.
 */
class AddModelInstance
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
        // Create an instance of the model.
        $route = $request->route('model');
        $model = $this->queryService->getModelInstanceForRoute($route);

        // Check if the model instance exists.
        if ($model === null) {
            return response()->json(['message' => config('constants.messages.http_404_model_class')], 404);
        }

        // Merge the model instance into the request data.
        $request->merge(['data-model' => $model]);

        // Continue processing the request.
        return $next($request);
    }
}
