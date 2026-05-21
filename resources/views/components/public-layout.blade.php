<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Policy Lookup – {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-gray-900">

    @php $setting = \App\Models\Setting::instance(); @endphp

    <header class="bg-orange-600 shadow">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center gap-x-4">
            @if($setting->logo_path)
                <img src="{{ Storage::url($setting->logo_path) }}" alt="Logo" class="h-10 w-auto object-contain">
            @endif
            <div>
                <h1 class="text-white text-lg font-bold leading-tight">{{ $setting->company_name ?? config('app.name') }}</h1>
                <p class="text-orange-200 text-xs">Policy Self-Service Portal</p>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        {{ $slot }}
    </main>

    <footer class="text-center text-xs text-gray-400 pb-8">
        &copy; {{ date('Y') }} {{ $setting->company_name ?? config('app.name') }}. All rights reserved.
    </footer>

</body>
</html>