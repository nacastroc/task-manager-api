<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Class PublicController
 *
 * This class handles requests to public endpoints (no user login required).
 */
class PublicController extends Controller
{
    /**
     * Renders the welcome page
     */
    public function welcome(Request $request)
    {
        $acceptHeader = $request->header('Accept');
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);

        $data = [
            'app' => 'Task Manager API',
            'version' => $composer['version'],
            'overview' => 'The TaskManager API is a simple task management system that allows users to create, update, delete, and retrieve tasks. Users need to authenticate to access the API.',
            'author' => 'Nestor Castro',
            'profile' => 'https://github.com/nacastroc/',
            'repository' => 'https://github.com/nacastroc/task-manager-api',
        ];

        // Check if JSON is preferred
        if ($acceptHeader && str_contains($acceptHeader, 'application/json')) {
            return response()->json($data, 200);
        }

        // If HTML is preferred or the Accept header is not provided
        return view('welcome', $data);
    }

    /**
     * Returns the current version of the app from the composer.json file.
     */
    public function version()
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        return response()->json(['version' => $composer['version']]);
    }
}
