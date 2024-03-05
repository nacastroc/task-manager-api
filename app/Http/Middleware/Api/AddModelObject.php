<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware to add model instance object to request data.
 *
 * This middleware fetches an object instance of a model
 * for a `/{model}/{id}` route, and adds it to the request data
 * for further processing. It must be preceded by the usage
 * of the `\App\Http\Middleware\Api\AddModelObject::class` middleware.
 */
class AddModelObject
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $id = $request->route('id');
        $model = $request->input('data-model');
        $columns = $request->input('data-select-columns');
        $with = $request->input('data-select-with');

        // Get the model instance object for the given id.
        $modelObject = null;
        if ($columns && $with) {
            $modelObject = $model::with($with)->find($id, $columns);
        } elseif ($columns && !$with) {
            $modelObject = $model::find($id, $columns);
        } else {
            $modelObject = $model::find($id);
        }

        if (!$modelObject) {
            return response()->json(['message' => config('constants.messages.http_404_model_object')], 404);
        }

        // Add the model instance to the request data.
        $request->merge(['data-model-object' => $modelObject]);
        return $next($request);
    }
}
