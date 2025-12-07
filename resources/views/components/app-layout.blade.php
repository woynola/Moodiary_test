<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Moodiary') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#F6F5F2]">
    <div class="min-h-screen flex flex-col">
        @include('layouts.navigation')

        <main class="flex-1">
            @if (session('success'))
                <div class="bg-[#D1F2EB] border border-[#A5B4FC] text-[#8B8EAB] px-4 py-3 rounded-lg m-4 animate-fade-in">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-[#FFE8E8] border border-[#A5B4FC] text-[#8B8EAB] px-4 py-3 rounded-lg m-4 animate-fade-in">
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
