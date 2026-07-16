@php
    // Single source of truth for the sidebar — rendered by both the mobile
    // drawer and the desktop rail below.
    $navItems = [
        [
            'route' => 'dashboard', 'active' => 'dashboard', 'label' => 'Dashboard',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />',
        ],
        [
            'route' => 'clients.create', 'active' => 'clients.create', 'label' => 'Add New Client',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />',
        ],
        [
            'route' => 'clients.index', 'active' => 'clients.index', 'label' => 'Clients',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />',
        ],
        [
            'route' => 'clients.expiring', 'active' => 'clients.expiring', 'label' => 'Expiring Clients',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />',
        ],
        [
            'route' => 'whatsapp.index', 'active' => 'whatsapp.index', 'label' => 'WhatsApp Log',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />',
        ],
        [
            'route' => 'quote-requests.index', 'active' => 'quote-requests.*', 'label' => 'Request Sebut Harga',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />',
        ],
        [
            'route' => 'payments.index', 'active' => 'payments.*', 'label' => 'Payments',
            'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />',
        ],
    ];

    $settingsIcon = '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />';

    $linkClasses = 'group flex gap-x-3 rounded-md p-2 text-sm font-semibold leading-6 text-orange-200 hover:bg-orange-700 hover:text-white';
@endphp
<!DOCTYPE html>
<html class="h-full bg-white">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full">
        <div x-data="layout">
            <!-- Off-canvas menu for mobile -->
            <div
                class="relative z-50 lg:hidden"
                role="dialog"
                aria-modal="true"
                x-show="isSidebarOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                x-cloak
            >
                <div
                    class="fixed inset-0 bg-gray-900/80"
                    x-show="isSidebarOpen"
                    x-transition:enter="transition-opacity ease-linear duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition-opacity ease-linear duration-300"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="closeSidebar"
                ></div>

                <div class="fixed inset-0 flex">
                    <div
                        class="relative mr-16 flex w-full max-w-xs flex-1"
                        x-show="isSidebarOpen"
                        x-transition:enter="transition ease-in-out duration-300 transform"
                        x-transition:enter-start="-translate-x-full"
                        x-transition:enter-end="translate-x-0"
                        x-transition:leave="transition ease-in-out duration-300 transform"
                        x-transition:leave-start="translate-x-0"
                        x-transition:leave-end="-translate-x-full"
                    >
                        <!-- Close button -->
                        <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
                            <button type="button" class="-m-2.5 p-2.5" @click="closeSidebar">
                                <span class="sr-only">Close sidebar</span>
                                <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Sidebar component for mobile -->
                        <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-orange-600 px-6 pb-4">
                            <div class="flex h-16 shrink-0 items-center">
                                <h1 class="text-white text-2xl font-bold">NAN SOLUTIONS</h1>
                            </div>
                            <nav class="flex flex-1 flex-col">
                                <ul role="list" class="flex flex-1 flex-col gap-y-7">
                                    <li>
                                        <ul role="list" class="-mx-2 space-y-1">
                                            @foreach ($navItems as $item)
                                                <li>
                                                    <a href="{{ route($item['route']) }}" @click="closeSidebar"
                                                       class="{{ $linkClasses }} {{ request()->routeIs($item['active']) ? 'bg-orange-700 text-white' : '' }}">
                                                        <svg class="size-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">{!! $item['icon'] !!}</svg>
                                                        {{ $item['label'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </li>
                                    <li class="mt-auto">
                                        <a href="{{ route('settings.edit') }}" @click="closeSidebar"
                                           class="{{ $linkClasses }} -mx-2 {{ request()->routeIs('settings.edit') ? 'bg-orange-700 text-white' : '' }}">
                                            <svg class="size-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">{!! $settingsIcon !!}</svg>
                                            Settings
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Static sidebar for desktop -->
            <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:flex-col transition-all duration-200"
                 :class="isSidebarCollapsed ? 'lg:w-20' : 'lg:w-72'">
                <div class="flex grow flex-col gap-y-5 overflow-y-auto overflow-x-hidden bg-orange-600 pb-4 transition-all duration-200"
                     :class="isSidebarCollapsed ? 'px-3' : 'px-6'">
                    <div class="flex h-16 shrink-0 items-center" :class="isSidebarCollapsed ? 'justify-center' : ''">
                        <img class="h-8 w-auto shrink-0" src="{{ asset('images/logo.png') }}" alt="NAN Solutions">
                        <h1 x-show="!isSidebarCollapsed" class="pl-2 text-white text-xl font-bold whitespace-nowrap">NAN SOLUTIONS</h1>
                    </div>
                    <nav class="flex flex-1 flex-col">
                        <ul role="list" class="flex flex-1 flex-col gap-y-7">
                            <li>
                                <ul role="list" class="-mx-2 space-y-1">
                                    @foreach ($navItems as $item)
                                        <li>
                                            <a href="{{ route($item['route']) }}"
                                               :title="isSidebarCollapsed ? @js($item['label']) : ''"
                                               class="{{ $linkClasses }} {{ request()->routeIs($item['active']) ? 'bg-orange-700 text-white' : '' }}"
                                               :class="isSidebarCollapsed ? 'justify-center' : ''">
                                                <svg class="size-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">{!! $item['icon'] !!}</svg>
                                                <span x-show="!isSidebarCollapsed" class="whitespace-nowrap">{{ $item['label'] }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                            <li class="mt-auto -mx-2 space-y-1">
                                <a href="{{ route('settings.edit') }}"
                                   :title="isSidebarCollapsed ? 'Settings' : ''"
                                   class="{{ $linkClasses }} {{ request()->routeIs('settings.edit') ? 'bg-orange-700 text-white' : '' }}"
                                   :class="isSidebarCollapsed ? 'justify-center' : ''">
                                    <svg class="size-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">{!! $settingsIcon !!}</svg>
                                    <span x-show="!isSidebarCollapsed" class="whitespace-nowrap">Settings</span>
                                </a>

                                <!-- Collapse toggle (desktop only) -->
                                <button type="button" @click="toggleSidebarCollapse()"
                                        :title="isSidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                                        class="{{ $linkClasses }} w-full"
                                        :class="isSidebarCollapsed ? 'justify-center' : ''">
                                    <svg class="size-6 shrink-0 transition-transform duration-200" :class="isSidebarCollapsed ? 'rotate-180' : ''"
                                         fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
                                    </svg>
                                    <span x-show="!isSidebarCollapsed" class="whitespace-nowrap">Collapse</span>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>

            <div class="transition-all duration-200" :class="isSidebarCollapsed ? 'lg:pl-20' : 'lg:pl-72'">
                <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                    <button type="button" @click="toggleSidebar" class="-m-2.5 p-2.5 text-gray-700 lg:hidden">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>

                    <!-- Separator -->
                    <div class="h-6 w-px bg-gray-900/10 lg:hidden" aria-hidden="true"></div>

                    <!-- User menu -->
                    <div class="flex flex-1 justify-end">
                        <div class="flex items-center gap-x-4 lg:gap-x-6">
                            <div class="relative">
                                <button
                                    type="button"
                                    class="-m-1.5 flex items-center p-1.5"
                                    id="user-menu-button"
                                    @click="toggleUserMenu"
                                    aria-expanded="false"
                                    aria-haspopup="true"
                                >
                                    <span class="sr-only">Open user menu</span>
                                    <!-- Mobile: initial only, so the menu is still reachable on a narrow screen -->
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-orange-600 text-sm font-semibold text-white lg:hidden">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </span>
                                    <span class="hidden lg:flex lg:items-center">
                                        <span class="ml-4 text-sm font-semibold leading-6 text-gray-900" aria-hidden="true">{{ Auth::user()->name }}</span>
                                        <svg class="ml-2 size-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </button>

                                <!-- Dropdown menu -->
                                <div
                                    x-show="isUserMenuOpen"
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="transform opacity-0 scale-95"
                                    x-transition:enter-end="transform opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="transform opacity-100 scale-100"
                                    x-transition:leave-end="transform opacity-0 scale-95"
                                    @click.away="closeUserMenu"
                                    class="absolute right-0 z-10 mt-2.5 w-32 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none"
                                    role="menu"
                                    aria-orientation="vertical"
                                    aria-labelledby="user-menu-button"
                                    tabindex="-1"
                                >
                                    <a href="{{ route('profile.edit') }}" class="block px-3 py-1 text-sm leading-6 text-gray-900" role="menuitem">Your Profile</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full px-3 py-1 text-left text-sm leading-6 text-gray-900" role="menuitem">Sign Out</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <main class="py-6 sm:py-10">
                    <div class="px-4 sm:px-6 lg:px-8">
                        @if (isset($header))
                            <div class="mb-6">
                                {{ $header }}
                            </div>
                        @endif
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        <style>[x-cloak]{display:none!important}</style>
    </body>
</html>
