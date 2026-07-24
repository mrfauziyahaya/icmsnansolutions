<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ site()->companyName() }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-gray-900">

    @php
        $company   = site()->companyName();
        $logo      = site()->logoUrl();
        $copyright = site()->copyrightName();
    @endphp

    <header class="bg-orange-600 shadow">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center gap-x-4">
            @if($logo)
                <img src="{{ $logo }}" alt="{{ $company }}" class="h-10 w-auto object-contain">
            @endif
            <div>
                <h1 class="text-white text-lg font-bold leading-tight">{{ $company }}</h1>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {{ $slot }}
    </main>

    <footer class="text-center text-xs text-gray-400 pb-8">
        &copy; {{ date('Y') }} {{ $copyright }}. All rights reserved.
    </footer>

</body>
</html>
