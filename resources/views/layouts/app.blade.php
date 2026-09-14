<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'OpenCash — aplikasi pengelolaan kas kelas yang rapi dan transparan untuk bendahara dan siswa.')">

    <title>@yield('title', config('app.name', 'OpenCash'))</title>

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-bg text-ink antialiased">
    @yield('content')

    @stack('scripts')
</body>
</html>