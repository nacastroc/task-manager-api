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
        $model = $request->input('data-model');
        $table = $model->getTable();
        $request->validate([
            'filter' => 'string|regex:/^\[(([a-z_][a-z0-9_]*)=([^,]*),?)+\]$/',
        ]);

        // Get query params.
        // Pagination.
        $page = $request->input('page', 1); // Page number.
        $perPage = $request->input('per_page', 10); // Number of items per page.
        // Columns for select
        $columns = $request->input('data-select-columns');
        // Associations for select
        $with = $request->input('data-select-with');

        // Query to fetch items.
        $query = $model->query();

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
                if (!in_array($key, $queryService->getValidColumns($table))) {
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
