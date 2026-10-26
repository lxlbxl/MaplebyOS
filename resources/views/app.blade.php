<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ $app_name ?? \Modules\Core\Entities\Setting::get('app_name', config('app.name', 'Laravel')) }}
    </title>

    <link rel="manifest" href="/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Mapleby">
    <meta name="theme-color" content="#faf9f6">

    @if(isset($favicon))
        <link rel="icon" href="{{ $favicon }}" type="image/x-icon" />
    @else
        <link rel="icon" href="/favicon.ico" />
    @endif

    <script>
        window.appName = "{{ $app_name ?? \Modules\Core\Entities\Setting::get('app_name', config('app.name', 'Laravel')) }}";
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    @routes
    @viteReactRefresh
    @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
    @inertiaHead
</head>

<body class="h-full font-sans antialiased text-sage-900 bg-cream-50 selection:bg-sage-500 selection:text-white">
    @inertia
</body>

</html>