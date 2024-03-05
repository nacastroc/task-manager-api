<?php

namespace App\Http\Middleware\Api;

use App\Services\QueryService;
use Closure;
use Illuminate\Http\Request;

/**
 * Middleware to handle selecting specific columns and eager loading associations for an Eloquent model.
 *
 * This middleware is responsible for extracting the model instance from the request,
 * selecting specified columns, and eager loading associations based on the incoming request.
 * It performs validation to ensure that the specified columns and associations are valid
 * for the given model. If invalid columns or associations are provided, it returns a 422 Unprocessable Entity response.
 */
class SelectColumns
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
        // Get model instance from request (require AddModelInstance middleware to precede this one).
        $model = $request->input('data-model');
        $table = $model->getTable();

        // Select columns.
        $columns = explode(',', $request->input('columns', '*')); // Columns to be selected.

        // Associations to be eager loaded.
        $with = $request->input('with') ? explode(',', $request->input('with')) : [];

        // Validate columns.
        $validColumns = $this->queryService->getValidColumns($table);
        if ($columns != ['*']) {
            $invalidColumns = array_diff($columns, $validColumns);
            if (!empty($invalidColumns)) {
                return response()->json(['message' => 'Invalid columns: ' . implode(', ', $invalidColumns)], 422);
            }
            if (count($with) > 0) {
                $columns = $this->queryService->appendKeysToSelect($validColumns, $columns);
            }
        }

        // Validate associations.
        $invalidAssociations = array_diff($with, $model->getRelations());
        if (!empty($invalidAssociations)) {
            return response()->json(['message' => 'Invalid associations: ' . implode(', ', $invalidAssociations)], 422);
        }

        $request->merge(['data-select-columns' => $columns]);
        $request->merge(['data-select-with' => $with]);

        return $next($request);
    }
}
