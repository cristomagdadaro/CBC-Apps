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
            <div class="flex gap-2 justify-center">
                <div class="flex justify-center mb-8">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-yellow-100 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-800">
                        <span class="text-sm font-semibold text-yellow-700 dark:text-yellow-400 uppercase tracking-wider">WARNING!</span>
                    </div>
                </div>
                <!-- Status Badge -->
                <div class="flex justify-center mb-8">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                        </span>
                        <span class="text-sm font-semibold text-red-700 dark:text-red-400 uppercase tracking-wider">Unauthorized Access</span>
                    </div>
                </div>
            </div>

            <!-- Footer Links -->
            <div class="mt-8 text-center">
                <div class="flex flex-wrap justify-center gap-4 text-xs text-gray-500 dark:text-gray-500 mb-4">
                    <span class="px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">HTTP 403</span>
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