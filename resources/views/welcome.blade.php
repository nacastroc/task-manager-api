<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Task Manager API</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1 class="mt-5">Task Manager API</h1>
        <h2 class="mt-4">Project Overview</h2>
        <p>The TaskManager API is a simple task management system that allows users to create, update, delete, and retrieve tasks. Users need to authenticate to access the API.</p>
        <h3 class="mt-4">Repository</h3>
        <p><a href="https://github.com/nacastroc/task-manager-api">https://github.com/nacastroc/task-manager-api</a></p>
        <h3 class="mt-4">Author</h3>
        <p>Nestor Castro</p>
        <h4 class="mt-2">GitHub</h4>
        <p><a href="https://github.com/nacastroc">https://github.com/nacastroc</a></p>
        <p>Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</p>
    </div>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>

</html>
