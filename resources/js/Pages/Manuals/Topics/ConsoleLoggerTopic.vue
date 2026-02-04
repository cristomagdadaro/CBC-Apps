<template>
    <div class="space-y-6">
        <section>
            <h3 class="text-lg font-bold mb-3">Unified Console Logger Service</h3>
            <p class="mb-3">CBC-Apps includes a centralized console logging service that automatically enables/disables based on the application environment. This ensures debug logs appear in development but are completely hidden in production.</p>
        </section>

        <section>
            <h3 class="text-lg font-bold mb-3">For Developers</h3>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">How It Works</h4>
                <ul class="list-disc list-inside space-y-2 text-sm">
                    <li><strong>Local/Development:</strong> All logs are displayed in browser console</li>
                    <li><strong>Production:</strong> All logs are silently ignored (zero performance impact)</li>
                    <li><strong>Environment Detection:</strong> Automatically reads from <code class="bg-gray-100 px-1">__APP_ENV__</code> or <code class="bg-gray-100 px-1">VITE_APP_ENV</code></li>
                    <li><strong>Manual Control:</strong> Can be toggled programmatically if needed</li>
                </ul>
            </div>

            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Basic Usage</h4>
                <p class="text-sm mb-2">Import and use the logger in any Vue component or JavaScript file:</p>
                <pre class="bg-gray-900 text-gray-100 p-3 rounded text-xs overflow-x-auto"><code>import ConsoleLogger from '@/Modules/shared/infrastructure/ConsoleLogger';

// Standard logging
ConsoleLogger.log('User data loaded');
ConsoleLogger.info('Processing started');
ConsoleLogger.warn('Deprecated API endpoint');
ConsoleLogger.error('Failed to fetch data');
ConsoleLogger.debug('Variable value:', someVar);</code></pre>
            </div>

            <div class="bg-purple-50 border-l-4 border-purple-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Available Methods</h4>
                <div class="text-sm space-y-2">
                    <p><strong>Basic Logging:</strong></p>
                    <ul class="list-disc list-inside ml-2 space-y-1 text-xs">
                        <li><code class="bg-gray-100 px-1">log(...args)</code> – Standard logging</li>
                        <li><code class="bg-gray-100 px-1">info(...args)</code> – ℹ️ Info level</li>
                        <li><code class="bg-gray-100 px-1">warn(...args)</code> – ⚠️ Warning level</li>
                        <li><code class="bg-gray-100 px-1">error(...args)</code> – ❌ Error level</li>
                        <li><code class="bg-gray-100 px-1">debug(...args)</code> – 🐛 Debug level</li>
                    </ul>

                    <p class="mt-3"><strong>Advanced Methods:</strong></p>
                    <ul class="list-disc list-inside ml-2 space-y-1 text-xs">
                        <li><code class="bg-gray-100 px-1">table(data)</code> – Display data as table</li>
                        <li><code class="bg-gray-100 px-1">group(label)</code> – Start collapsible group</li>
                        <li><code class="bg-gray-100 px-1">groupEnd()</code> – End group</li>
                        <li><code class="bg-gray-100 px-1">time(label)</code> – Start performance timer</li>
                        <li><code class="bg-gray-100 px-1">timeEnd(label)</code> – End timer and log duration</li>
                        <li><code class="bg-gray-100 px-1">assert(condition, message)</code> – Conditional logging</li>
                    </ul>

                    <p class="mt-3"><strong>Utility Methods:</strong></p>
                    <ul class="list-disc list-inside ml-2 space-y-1 text-xs">
                        <li><code class="bg-gray-100 px-1">isLoggingEnabled()</code> – Check if logging is active</li>
                        <li><code class="bg-gray-100 px-1">getEnvironment()</code> – Get current APP_ENV</li>
                        <li><code class="bg-gray-100 px-1">setEnabled(boolean)</code> – Toggle logging on/off</li>
                    </ul>
                </div>
            </div>

            <div class="bg-pink-50 border-l-4 border-pink-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Practical Examples</h4>
                <pre class="bg-gray-900 text-gray-100 p-3 rounded text-xs overflow-x-auto"><code>import ConsoleLogger from '@/Modules/shared/infrastructure/ConsoleLogger';

// Example 1: API Response Logging
async function fetchUserData(userId) {
    try {
        ConsoleLogger.log('Fetching user:', userId);
        const response = await api.get(`/users/${userId}`);
        ConsoleLogger.info('User data received:', response);
        return response;
    } catch (error) {
        ConsoleLogger.error('Failed to fetch user:', error);
        throw error;
    }
}

// Example 2: Performance Measurement
function processLargeDataset(data) {
    ConsoleLogger.time('data-processing');
    // ... do work
    ConsoleLogger.timeEnd('data-processing');
}

// Example 3: Grouped Logs
function handleFormSubmission(formData) {
    ConsoleLogger.group('Form Submission');
    ConsoleLogger.log('Form values:', formData);
    ConsoleLogger.log('Validation passed');
    ConsoleLogger.log('Sending to server...');
    ConsoleLogger.groupEnd();
}

// Example 4: Table Output
function displayUserList(users) {
    ConsoleLogger.table(users);
}

// Example 5: Conditional Logging
function validateInput(value) {
    ConsoleLogger.assert(
        value.length > 0, 
        'Input cannot be empty'
    );
}</code></pre>
            </div>

            <div class="bg-orange-50 border-l-4 border-orange-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Configuration & Environment Detection</h4>
                <p class="text-sm mb-2">The logger automatically detects these environments:</p>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li><strong>Enabled:</strong> 'local', 'development'</li>
                    <li><strong>Disabled:</strong> 'staging', 'production', or any other value</li>
                </ul>
                <p class="text-sm mt-3">The environment is read from (in order):</p>
                <ol class="list-decimal list-inside space-y-1 text-sm">
                    <li><code class="bg-gray-100 px-1">window.__APP_ENV__</code></li>
                    <li><code class="bg-gray-100 px-1">VITE_APP_ENV</code> environment variable</li>
                    <li><code class="bg-gray-100 px-1">import.meta.env.MODE</code></li>
                    <li>Defaults to: <code class="bg-gray-100 px-1">'production'</code></li>
                </ol>
            </div>

            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                <h4 class="font-semibold mb-2">Integration with ApiService</h4>
                <p class="text-sm mb-2">The ConsoleLogger is already integrated into ApiService.ts for automatic API debugging:</p>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li>GET/POST/PUT/DELETE requests log automatically</li>
                    <li>Request parameters are logged</li>
                    <li>Response data is logged</li>
                    <li>Errors are logged with details</li>
                </ul>
                <p class="text-sm mt-2">No configuration needed—just use ApiService as normal!</p>
            </div>
        </section>

        <section>
            <h3 class="text-lg font-bold mb-3">File Location</h3>
            <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded text-sm">
                <p><strong>Implementation:</strong> <code class="bg-gray-100 dark:bg-gray-600 px-1">resources/js/Modules/shared/infrastructure/ConsoleLogger.ts</code></p>
                <p class="mt-2"><strong>Used in:</strong> <code class="bg-gray-100 dark:bg-gray-600 px-1">resources/js/Modules/infrastructure/ApiService.ts</code></p>
            </div>
        </section>

        <section>
            <h3 class="text-lg font-bold mb-3">Benefits</h3>
            <div class="space-y-2">
                <div class="border-l-4 border-green-400 bg-green-50 p-3">
                    <p><strong>Zero Performance Impact:</strong> In production, logging calls are completely bypassed</p>
                </div>
                <div class="border-l-4 border-green-400 bg-green-50 p-3">
                    <p><strong>Consistent Formatting:</strong> All logs include timestamps and level indicators</p>
                </div>
                <div class="border-l-4 border-green-400 bg-green-50 p-3">
                    <p><strong>Easy to Use:</strong> Simple import and method calls throughout the app</p>
                </div>
                <div class="border-l-4 border-green-400 bg-green-50 p-3">
                    <p><strong>Environment-Aware:</strong> Automatic detection—no configuration needed</p>
                </div>
                <div class="border-l-4 border-green-400 bg-green-50 p-3">
                    <p><strong>Flexible Control:</strong> Can manually enable/disable logging at runtime if needed</p>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
export default {
    name: 'ConsoleLoggerTopic',
}
</script>
