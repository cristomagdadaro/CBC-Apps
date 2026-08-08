<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dev View Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8 font-sans">
    <div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        <h1 class="text-3xl font-bold mb-8 text-gray-800 flex items-center">
            <svg class="w-8 h-8 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            Blade View Previewer
        </h1>
        
        <div class="mb-6 p-4 bg-blue-50 text-blue-800 rounded-lg text-sm">
            <strong>Tip:</strong> If a view fails to load due to undefined variables, you can add mock data for it in the <code>getMockData()</code> method of <code>App\Http\Controllers\DevPreviewController</code>.
        </div>
        
        @foreach($views as $dir => $files)
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4 text-gray-700 border-b pb-2 capitalize">{{ str_replace('/', ' / ', $dir) }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($files as $viewName)
                        <a href="{{ route('dev.views.show', ['view' => $viewName]) }}" 
                           target="_blank"
                           class="flex items-center p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50 text-gray-600 hover:text-blue-600 transition-colors">
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            <span class="truncate">{{ $viewName }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</body>
</html>
