<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

class ApiController extends Controller
{
    // Helper functions.

    /**
     * Helper function to get valid columns for the model of a specified type
     *
     * @param Model $model Model instance
     * @param string $type Column type
     */
    private function getValidColumns($model, $type = null)
    {
        $table = $model->getTable();
        $columns = Schema::getColumnListing($table);
        $filteredColumns = [];

        foreach ($columns as $column) {
            $columnType = Schema::getColumnType($table, $column);
            if ($columnType == $type) {
                $filteredColumns[] = $column;
            }
        }

        return $type ? $filteredColumns : $columns;
    }


    /**
     * Return new instance of model based on route parameter.
     * `$model = getModelInstanceForRoute('users'); // Returns new User`
     *
     * @param string route The route callback for the model.
     */
    private function getModelInstanceForRoute($route)
    {
        switch ($route) {
            case 'users':
                return new User;
                break;
            case 'tasks':
                return new Task;
                break;
            default:
                return null;
                break;
        }
    }

    public function save()
    {
        // TODO: model instance save logic
    }

    // Controller endpoint functions.

    public function list(Request $request)
    {
        $modelName = $request->route('model');

        // Create an instance of the model.
        $model = $this->getModelInstanceForRoute($modelName);

        if ($model === null) {
            return response()->json(['message' => 'Model class not found'], 404);
        }

        // Get query params.
        $page = $request->input('page', 1); // Page number.
        $perPage = $request->input('per_page', 10); // Number of items per page.
        $columns = explode(',', $request->input('columns', '*')); // Columns to be selected.
        $with = $request->input('with') ? explode(',', $request->input('with')) : []; // Associations to be eager loaded.

        // Validate columns
        $validColumns = $this->getValidColumns($model);
        if ($columns != ['*']) {
            $invalidColumns = array_diff($columns, $validColumns);
            if (!empty($invalidColumns)) {
                return response()->json(['message' => 'Invalid columns: ' . implode(', ', $invalidColumns)], 422);
            }
        }

        // Validate associations
        $invalidAssociations = array_diff($with, $model->getRelations());
        if (!empty($invalidAssociations)) {
            return response()->json(['message' => 'Invalid associations: ' . implode(', ', $invalidAssociations)], 422);
        }

        // Query to fetch items
        $query = $model->query();

        // Select specific columns
        $query->select($columns);

        // Eager load associations
        $query->with($with);

        // TODO: Search by attribute value

        // TODO: Generic string search by selected columns
        $searchString = $request->input('search');
        if ($searchString) {
            $validSearchColumns = $this->getValidColumns($model, 'varchar');
            $searchColumns = $columns != ['*'] ? array_intersect($columns, $validSearchColumns) : $validSearchColumns;
            foreach ($searchColumns as $column) {
                $query->orWhere($column, 'LIKE', '%' . $searchString . '%');
            }
        }

        // Get results
             $items = $perPage > 0
            ? $query->paginate($perPage, $columns, 'page', $page)
            : $query->get();

        return response()->json($items);
    }

    public function show(Request $request)
    {
        // TODO: implement show model by id
        return response()->json([
            'message' => 'TODO'
        ]);
    }

    public function add(Request $request)
    {
        // TODO: implement add model
        return response()->json([
            'message' => 'TODO'
        ]);
    }

    public function edit(Request $request)
    {
        // TODO: implement edit model
        return response()->json([
            'message' => 'TODO'
        ]);
    }

    public function delete(Request $request)
    {
        // TODO: implement delete model by id
        return response()->json([
            'message' => 'TODO'
        ]);
    }
}
