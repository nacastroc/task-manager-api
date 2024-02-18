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
        'http_200_logout' => 'Logged out successfully.',
        'http_401' => 'Unauthenticated.',
        'http_401_invalid_credentials' => 'Invalid email or password.',
        'http_403' => 'Unauthorized.',
        'http_403_verify_email' => 'Your email address is not verified.',
    ]
];
