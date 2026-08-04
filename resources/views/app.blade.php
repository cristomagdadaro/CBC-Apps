<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta property="og:image" content="{{ asset('imgs/philrice-cbc-compound.jpg') }}">
        <meta property="og:image:secure_url" content="{{ secure_asset('imgs/philrice-cbc-compound.jpg') }}">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:alt" content="PhilRice CBC compound aerial view">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:image" content="{{ asset('imgs/philrice-cbc-compound.jpg') }}">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
        <script>
            window.__CBC_REALTIME__ = @json(config('realtime'));
        </script>
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>

<style>
    @keyframes fall {
        to {
            transform: translateY(100vh); /* Move to bottom */
            opacity: 0;
        }
    }

    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    @keyframes rotateGradient {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }

    :root {
        --gradient-start: #42C2FF;
        --gradient-end: #EC8305;
    }

    .dark {
        --gradient-start: #0F172A;
        --gradient-end: #334155;
    }

    .bg-gradient-radial {
        background: radial-gradient(circle, var(--gradient-start), var(--gradient-end));
        background-size: 200% 200%;
        position: absolute;
        width: 300%;
        height: 300%;
        min-width: 3000px;
        top: -90%;
        left: -100%;
        animation: rotateGradient 10s linear infinite;
    }

</style>


<style>
    #falling-logos {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        pointer-events: none; /* Prevent interaction */
    }

    .falling-logo {
        position: absolute;
        top: -50px; /* Start slightly above viewport */
        animation: fall linear infinite;
    }
</style>
