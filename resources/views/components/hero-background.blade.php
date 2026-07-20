<section
    {{ $attributes->merge([
        'class' => 'hero-background relative isolate min-h-[720px] overflow-hidden'
    ]) }}
>
    {{-- Soft yellow glow behind the left-side content --}}
    <div
        class="pointer-events-none absolute left-[12%] top-[10%] h-[500px] w-[500px]
               rounded-full bg-yellow-300/25 blur-[120px]"
    ></div>

    {{-- Red glow on the right --}}
    <div
        class="pointer-events-none absolute -right-32 top-10 h-[620px] w-[620px]
               rounded-full bg-red-600/30 blur-[150px]"
    ></div>

    {{-- Subtle central orange glow --}}
    <div
        class="pointer-events-none absolute left-1/2 top-1/2 h-[460px] w-[700px]
               -translate-x-1/2 -translate-y-1/2 rounded-full
               bg-orange-400/20 blur-[130px]"
    ></div>

    {{-- Optional subtle texture --}}
    <div class="hero-background__texture pointer-events-none absolute inset-0"></div>

    {{-- Page content goes here. w-full so it still spans the section when the
         section is a flex container (otherwise the flex item shrink-wraps and
         any mx-auto inside centres against the wrong width). --}}
    <div class="relative z-10 w-full">
        {{ $slot }}
    </div>

    {{-- Bottom decorative waves --}}
    <div class="pointer-events-none absolute inset-x-0 bottom-0 z-0">
        <svg
            viewBox="0 0 1440 150"
            preserveAspectRatio="none"
            class="block h-[110px] w-full md:h-[145px]"
            aria-hidden="true"
        >
            <path
                d="M0,78
                   C110,52 170,52 270,83
                   C355,109 420,54 520,83
                   C640,120 725,51 835,70
                   C955,91 1000,42 1100,54
                   C1200,66 1295,97 1440,54
                   L1440,150
                   L0,150 Z"
                fill="rgba(228, 85, 18, 0.62)"
            />

            <path
                d="M0,112
                   C120,70 215,131 320,104
                   C430,74 500,126 615,104
                   C735,80 825,75 930,98
                   C1050,126 1140,67 1245,84
                   C1335,97 1390,81 1440,94
                   L1440,150
                   L0,150 Z"
                fill="rgba(204, 81, 18, 0.58)"
            />
        </svg>
    </div>
</section>
