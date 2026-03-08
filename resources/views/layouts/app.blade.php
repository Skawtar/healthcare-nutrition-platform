<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Medical System') }}</title>
     <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0.0"></script> <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1"></script> </head>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.46.0/dist/apexcharts.min.js"></script>

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-gray-50">

    <!-- Navigation -->
 @auth
        @if(request()->is('admin*') || (auth()->user()->role === 'admin'))
            @include('admin.layouts.sidebar')
        @elseif(request()->is('medecin*') || (auth()->user()->role === 'medecin'))
            @include('medecin.layouts.sidebar')
        @endif
    @endauth
   
    <!-- Page Content -->
    <main>
        @yield('content')
    </main>

   
</body>
</html>