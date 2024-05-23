<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Task;
use App\Services\QueryService;
use App\Services\ValidatorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Class ApiController
 *
 * Handles generic model requests to the API routes.
 */
class ApiController extends Controller
{

    // Controller endpoint functions.

    /**
     * Returns a list, paginated or not, of a given model.
     *
     * @param Request $request
     * @param QueryService $queryService
     * @return JsonResponse
     */
    public function list(Request $request, QueryService $queryService, ValidatorService $validatorService)
    {
        $user = $request->user();
        $model = $request->input('data-model');
        $table = $model->getTable();

        $validatorService->validateListFilter($request);

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

        // Security check
        if (!$model instanceof User && !$user->admin) {
            if (!$filter) return response()->json(['message' => config('constants.messages.http_403')], 403);
            $filtersId = false;
            foreach ($pairs as $pair) {
                $pairData = explode('=', $pair);
                if ($pairData[0] === 'user_id' && $pairData[1] == $user->id) {
                    $filtersId = true;
                    break;
                }
            }
            if (!$filtersId) return response()->json(['message' => config('constants.messages.http_403')], 403);
        }

        // Generic string search.
        $searchString = $request->input('search');
        // Generic string search by selected columns.
        if ($searchString) {
            $validSearchColumns = $queryService->getValidColumns($table, ['string', 'text']);
            $searchColumns = $columns != ['*']
                ? array_intersect($columns, $validSearchColumns)
                : $validSearchColumns;
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

    /**
     * Returns an instance of a model object based on route id.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request)
    {
        $user = $request->user();
        $route = $request->route('model');
        $model = $request->input('data-model');
        $id = $request->route('id');

        // Columns for select
        $columns = $request->input('data-select-columns');

        // Associations for select
        $with = $request->input('data-select-with');

        $item = $model::with($with)->find($id, $columns);

        // Security first.
        if (!$model instanceof User && !$user->admin && $item && $item->user_id !== $user->id)
            response()->json(['message' => config('constants.messages.http_403')], 403);

        // Data presence later.
        if (!$item)
            return response()->json(['message' => "Object of {$route} with id {$id} not found"], 404);

        return response()->json($item);
    }

    public function create(Request $request, ValidatorService $validatorService)
    {
        $user = $request->user();
        $model = $request->input('data-model');

        if ($model instanceof Task) {
            $validData = $validatorService->validateTaskPost($request);
            $user->tasks()->create([
                'title' => $validData['title'],
                'description' => $validData['description'],
                'due_date' => $validData['due_date'],
            ]);
            return response()->json([
                'message' => config('constants.messages.http_201')
            ]);
        } else {
            response()->json(['message' => config('constants.messages.http_403')], 403);
        }
    }

    public function update(Request $request, ValidatorService $validatorService)
    {
        $model = $request->input('data-model');

        if ($model instanceof Task) {
            $task = $validatorService->validateTaskPut($request);
            $task->save();
        } else {
            response()->json(['message' => config('constants.messages.http_403')], 403);
        }

        return response()->json([
            'message' => config('constants.messages.http_200')
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'ids' =>  'required'
        ]);

        $model = $request->input('data-model');
        $ids = explode(',', $request->ids);
        $model::destroy($ids);

        return response()->json([
            'message' => config('constants.messages.http_200')
        ]);
    }
}
