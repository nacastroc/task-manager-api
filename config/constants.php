<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Message constants
    |--------------------------------------------------------------------------
    |
    | This option defines the strings to use as response messages
    | across the application. Ensures consistency and enables updating, testing
    | and maintenance.
    |
    */
    'messages' => [
        // HTTP responses
        'http_200' => 'OK.',
        'http_400' => 'Bad Request.',
        'http_401' => 'Unauthorized.',
        'http_401_invalid_credentials' => 'Invalid email or password.',
        'http_403' => 'Forbidden.',
        'http_403_verify_email' => 'Your email address is not verified.',
        'http_403_self_delete' => 'An user cannot delete itself.',
        'http_404' => 'Not found.',
        'http_404_model_class' => 'Model class not found.',
        'http_404_model_object' => 'Model object not found.',
        'http_405' => 'Method not allowed.',
        'http_500' => 'Internal server error.',
    ],
    'validation' => [
        'model_routes' => 'user|tasks'
    ]
];
