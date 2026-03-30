<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>OneCBC Portal | Under Maintenance</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            'AB': '#0f766e',
                            'AC': '#f59e0b',
                            'AA': '#10b981',
                            'AD': '#059669',
                        },
                        fontFamily: {
                            'montserrat': ['Montserrat', 'sans-serif'],
                        }
                    }
                }
            }
        </script>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            * {
                box-sizing: border-box;
            }
            body {
                margin: 0;
                min-height: 100vh;
                font-family: "Segoe UI", Arial, sans-serif;
            }
            .gradient-text {
                background: linear-gradient(135deg, #0f766e 0%, #059669 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            .dark .gradient-text {
                background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            .animate-pulse-slow {
                animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-10px); }
            }
            .animate-float {
                animation: float 3s ease-in-out infinite;
            }
            .gear-spin {
                animation: spin 8s linear infinite;
            }
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        </style>
    </head>
    <body class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-300 min-h-screen flex items-center justify-center p-4">
        
        <!-- Background Elements -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 left-0 w-full pointer-events-none h-full bg-gradient-to-br from-lime-100/30 via-transparent to-teal-100/30 dark:from-lime-900/20 dark:to-teal-900/20"></div>
            <div class="absolute top-20 left-10 w-72 h-72 bg-AC/10 rounded-full blur-3xl animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-AB/10 rounded-full blur-3xl animate-pulse-slow" style="animation-delay: 1s;"></div>
        </div>

        <main class="relative z-10 w-full max-w-6xl mx-auto">
            <div class="text-center mb-8">
                <!-- Logo/Title Section -->
                <div class="relative w-fit mx-auto mb-6">
                    <div class="flex items-center gap-2 justify-center">
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-none text-lime-500 dark:text-green-400 font-[Montserrat] drop-shadow-md">
                            OneCBC
                        </h1>
                    </div>
                    <span class="absolute -bottom-4 -right-6 text-xs text-lime-500 dark:text-green-400 font-semibold">
                        DA-Crop Biotechnology Center
                    </span>
                </div>
                <blockquote class="mt-2 font-semibold text-gray-500 dark:text-gray-500">
                    Better Crops, Better Lives
                </blockquote>
            </div>

            <!-- Status Badge -->
            <div class="flex justify-center mb-8">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                    </span>
                    <span class="text-sm font-semibold text-red-700 dark:text-red-400 uppercase tracking-wider">Service Unavailable</span>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="{{ url()->current() }}" class="group relative inline-flex items-center justify-center px-8 py-3 text-base font-bold text-white transition-all duration-200 bg-gradient-to-r from-AB to-AD rounded-xl hover:shadow-lg hover:shadow-AB/30 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-AB dark:focus:ring-offset-gray-900">
                    <svg class="w-5 h-5 mr-2 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Try Again
                </a>
                <a href="https://dacbc.philrice.gov.ph/" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center px-8 py-3 text-base font-bold text-gray-700 dark:text-gray-300 transition-all duration-200 bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-500 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 dark:focus:ring-offset-gray-900">
                    <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    Visit DA-CBC Website
                </a>
            </div>

            <!-- Footer Links -->
            <div class="mt-8 text-center">
                <div class="flex flex-wrap justify-center gap-4 text-xs text-gray-500 dark:text-gray-500 mb-4">
                    <span class="px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">HTTP 503</span>
                </div>
            </div>
        </main>

        <script>
            // Check for dark mode preference
            if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            }
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
                if (event.matches) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            });
        </script>
    </body>
</html>