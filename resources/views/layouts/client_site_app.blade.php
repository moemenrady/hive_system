<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Hive') }}</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @yield('style')

    <style>
        html {
            scroll-behavior: smooth;
        }

        /* زر الرجوع */
        .back-nav {
            max-width: 1200px;
            margin: 20px auto 0;
            padding: 0 40px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--olive-dark);
            font-weight: bold;
            transition: 0.3s;
            padding: 8px 0;
            border-bottom: 2px solid transparent;
        }

        .btn-back:hover {
            color: var(--sage);
            border-bottom-color: var(--sage);
        }
    </style>
</head>

<body class="font-sans antialiased">
    
    @yield('content')

    @stack('scripts')
</body>

</html>
