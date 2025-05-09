<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'YoPrint CSV Uploader') }} - @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-800">
<nav class="bg-white shadow mb-8">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <h1 class="text-xl font-semibold text-blue-600">{{ config('app.name', 'CSV Uploader') }}</h1>
    </div>
</nav>

<main class="px-4">
    @yield('content')
</main>
</body>
</html>
