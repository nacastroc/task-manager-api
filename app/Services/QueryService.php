<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Schema;
use Exception;

/**
 * Class QueryService
 *
 * This service class provides helper functions to use in API controllers.
 */
class QueryService
{
    /**
     * Return new instance of model based on route parameter.
     * `$model = getModelInstanceForRoute('users'); // Returns new User`
     *
     * @param string route The route callback for the model.
     */
    public function getModelInstanceForRoute($route)
    {
        switch ($route) {
            case 'user':
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

    /**
     * Helper function to get valid columns for the model of a specified type
     *
     * @param string $table Table associated with a model
     * @param string|array|null $type Column type or array of column types
     */
    public function getValidColumns($table, $type = null)
    {
        $columns = Schema::getColumnListing($table);
        $filteredColumns = [];

        if ($type !== null) {
            if (is_string($type)) {
                $types = [$type];
            } elseif (is_array($type)) {
                $types = $type;
            } else {
                throw new Exception('Invalid type. Type must be a string or an array of strings.');
            }
            foreach ($columns as $column) {
                $columnType = Schema::getColumnType($table, $column);
                if (in_array($columnType, $types)) {
                    $filteredColumns[] = $column;
                }
            }
        } else {
            $filteredColumns = $columns;
        }

        return $filteredColumns;
    }

    /**
     * Adjust $columns to work with associations by adding the 'id'
     * and any foreign key columns to the query select.
     *
     * @param array $validColumns The valid columns for the model.
     * @param array $columns The currently selected valid columns for the query.
     *
     * @return array The updated array of selected columns
     */
    public function appendKeysToSelect($validColumns, $columns)
    {
        // Add id and foreign keys if they are not included
        if (!in_array('id', $columns, true)) array_push($columns, 'id');
        foreach ($validColumns as $key => $value) {
            if (str_ends_with($value, '_id') && !in_array($value, $columns, true)) {
                array_push($columns, $value);
            }
        }
        return $columns;
    }

    /**
     * Cast the string $value to an integer, float, boolean, or string
     * based on the column type in the database.
     *
     * @param string $table Table associated with the model.
     * @param string $column Column name to check the value type.
     * @param string $value A string value to cast from a key-value pair.
     *
     * @return int|float|bool|string The casted value.
     */
    public function setColumnValueType($table, $column, $value)
    {
        $columnType = Schema::getColumnType($table, $column);
        // Cast $value according to database column type.
        switch ($columnType) {
            case 'integer':
                settype($value, 'int');
                break;
            case 'float':
                settype($value, 'float');
                break;
            case 'boolean':
                settype($value, 'bool');
                break;
            default:
                settype($value, 'string');
        }
        return $value;
    }
}
