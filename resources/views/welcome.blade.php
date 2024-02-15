<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $app }} v{{ $version }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <h1 class="mt-5">{{ $app }} <small>v{{ $version }}</small></h1>
        <h2 class="mt-4">Project Overview</h2>
        <p>{{ $overview }}</p>
        <h3 class="mt-4">Author</h3>
        <a href="{{ $profile }}">{{ $author }}</a>
        <h3 class="mt-4">Repository</h3>
        <p><a href="{{ $repository }}">{{ $repository }}</a></p>
        <p>Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</p>
    </div>

    <!-- Bootstrap JS and jQuery -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>

</html>
