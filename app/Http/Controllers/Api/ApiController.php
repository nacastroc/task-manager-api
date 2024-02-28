<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QueryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Class ApiController
 *
 * Handles generic model requests to the API routes.
 */
class ApiController extends Controller
{
    // Helper functions.

    public function save()
    {
        // TODO: model instance save logic
    }

    // Controller endpoint functions.

    /**
     * Returns a list, paginated or not, of a given model.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request, QueryService $queryService)
    {
        // Create an instance of the model.
        $modelName = $request->route('model');
        $model = $queryService->getModelInstanceForRoute($modelName);
        if ($model === null) {
            return response()->json(['message' => 'Model class not found'], 404);
        }
        $table = $model->getTable();

        $request->validate([
            'filter' => 'string|regex:/^\[(([a-z_][a-z0-9_]*)=([^,]*),?)+\]$/',
        ]);

        // Query to fetch items.
        $query = $model->query();

        // Get query params.
        // Pagination.
        $page = $request->input('page', 1); // Page number.
        $perPage = $request->input('per_page', 10); // Number of items per page.

        // Select columns.
        $columns = explode(',', $request->input('columns', '*')); // Columns to be selected.
        // Associations to be eager loaded.
        $with = $request->input('with') ? explode(',', $request->input('with')) : [];

        // Validate columns.
        $validColumns = $queryService->getValidColumns($table);
        if ($columns != ['*']) {
            $invalidColumns = array_diff($columns, $validColumns);
            if (!empty($invalidColumns)) {
                return response()->json(['message' => 'Invalid columns: ' . implode(', ', $invalidColumns)], 422);
            }
            if (count($with) > 0) {
                $columns = $queryService->appendKeysToSelect($validColumns, $columns);
            }
        }

        // Validate associations.
        $invalidAssociations = array_diff($with, $model->getRelations());
        if (!empty($invalidAssociations)) {
            return response()->json(['message' => 'Invalid associations: ' . implode(', ', $invalidAssociations)], 422);
        }

        // Select specific columns.
        $query->select($columns);

        // Eager load associations.
        $query->with($with);

        // Search by attribute value pairs in the filter input.
        $filter = $request->input('filter');
        if ($filter) {
            // Parse the filter parameter.
            $filter = trim($filter, '[]');
            $pairs = explode(',', $filter);
            foreach ($pairs as $pair) {
                list($key, $value) = explode('=', $pair);
                // Validate key in model columns.
                if (!in_array($key, $validColumns)) {
                    return response()->json(['message' => 'Invalid filter key: ' . $key], 422);
                }
                $value = $queryService->setColumnValueType($table, $key, $value);
                // Add filter key-value pair to the query's where clause.
                $query->where($key, $value);
            }
        }

        // Generic string search.
        $searchString = $request->input('search');
        // Generic string search by selected columns.
        if ($searchString) {
            $validSearchColumns = $queryService->getValidColumns($table, ['string', 'text']);
            $searchColumns = $columns != ['*'] ? array_intersect($columns, $validSearchColumns) : $validSearchColumns;
            foreach ($searchColumns as $column) {
                $query->orWhere($column, 'LIKE', '%' . $searchString . '%');
            }
        }

        // Get results.
        $items = $perPage > 0
            ? $query->paginate($perPage, $columns, 'page', $page)
            : $query->get();

        return response()->json($items);
    }

    public function show(Request $request)
    {
        // TODO: implement show model by id.
        return response()->json([
            'message' => 'TODO'
        ]);
    }

    public function create(Request $request)
    {
        // TODO: implement add model.
        return response()->json([
            'message' => 'TODO'
        ]);
    }

    public function update(Request $request)
    {
        // TODO: implement edit model.
        return response()->json([
            'message' => 'TODO'
        ]);
    }

    public function delete(Request $request)
    {
        // TODO: implement delete model by id or id batch.
        return response()->json([
            'message' => 'TODO'
        ]);
    }
}
