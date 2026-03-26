<template>
  <div class="space-y-6">
    <!-- Introduction -->
    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        What is the System Options Module?
      </h3>
      <p>
        The System Options Module allows you to manage system-wide settings, configuration values, and dropdown options directly from the database instead of hardcoding them in configuration files. This makes it easy to update settings without redeploying code.
      </p>
    </section>

    <!-- Key Features -->
    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        Key Features
      </h3>
      <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
        <li><strong>Dynamic Configuration</strong> - Update settings without code changes</li>
        <li><strong>Multiple Types</strong> - Support for text, numbers, booleans, JSON, select options, and more</li>
        <li><strong>Organized Groups</strong> - Group related options (system, email, inventory, etc.)</li>
        <li><strong>Search & Filter</strong> - Easily find options in the UI</li>
        <li><strong>Full CRUD</strong> - Create, read, update, and delete options</li>
        <li><strong>API Access</strong> - REST API endpoints for programmatic access</li>
        <li><strong>Audit Trail</strong> - Tracks creation and modification timestamps</li>
      </ul>
    </section>

    <!-- Where to Access -->
    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        Where to Access
      </h3>
      <p class="mb-3">
        You can manage system options from the admin panel:
      </p>
      <div class="bg-gray-100 dark:bg-gray-700 p-4 rounded-lg">
        <code class="text-gray-900 dark:text-gray-100">/apps/system/options</code>
      </div>
    </section>

    <!-- Available Option Groups -->
    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        Available Option Groups
      </h3>
      <div class="space-y-3">
        <div class="border-l-4 border-blue-500 pl-4">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100">System</h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">Application name, version, approving officers, center chief</p>
        </div>
        <div class="border-l-4 border-green-500 pl-4">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100">Email</h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">Email settings, sender information, notification emails</p>
        </div>
        <div class="border-l-4 border-purple-500 pl-4">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100">Inventory</h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">Stock levels, storage locations, transaction types, thresholds</p>
        </div>
        <div class="border-l-4 border-yellow-500 pl-4">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100">Forms</h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">Form submission deadlines, maximum slots</p>
        </div>
        <div class="border-l-4 border-red-500 pl-4">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100">Rental</h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">Vehicles, event halls, cancellation policies</p>
        </div>
        <div class="border-l-4 border-indigo-500 pl-4">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100">Requests</h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">Supply types, equipment types, materials, space types</p>
        </div>
        <div class="border-l-4 border-pink-500 pl-4">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100">Locations</h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">Office spaces, screenhouses, laboratories</p>
        </div>
        <div class="border-l-4 border-cyan-500 pl-4">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100">Reports</h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">Report templates with field definitions</p>
        </div>
      </div>
    </section>

    <!-- How to Create an Option -->
    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        How to Create an Option
      </h3>
      <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-300">
        <li>Go to <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">/apps/system/options</code></li>
        <li>Click the <strong>"+ New Option"</strong> button in the top right</li>
        <li>Fill in the form with:
          <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
            <li><strong>Key</strong> - Unique identifier (snake_case)</li>
            <li><strong>Label</strong> - Human-readable name</li>
            <li><strong>Description</strong> - What this option is for</li>
            <li><strong>Type</strong> - text, number, textarea, boolean, select, json, checkbox</li>
            <li><strong>Group</strong> - Which category to organize under</li>
            <li><strong>Value</strong> - The actual value or default</li>
          </ul>
        </li>
        <li>Click <strong>"Create Option"</strong> to save</li>
      </ol>
    </section>

    <!-- How to Edit an Option -->
    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        How to Edit an Option
      </h3>
      <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-300">
        <li>Go to <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">/apps/system/options</code></li>
        <li>Find the option in the table (use search or filters)</li>
        <li>Click the <strong>"Edit"</strong> button in the Actions column</li>
        <li>Update the fields you want to change
          <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
            <li><em>Note: Key and Type cannot be changed after creation</em></li>
          </ul>
        </li>
        <li>Click <strong>"Save Changes"</strong> to update</li>
      </ol>
    </section>

    <!-- How to Delete an Option -->
    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        How to Delete an Option
      </h3>
      <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-300">
        <li>Go to <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">/apps/system/options</code></li>
        <li>Find the option in the table</li>
        <li>Click the <strong>"Delete"</strong> button in the Actions column</li>
        <li>Confirm the deletion in the modal dialog</li>
        <li><strong>⚠️ Warning:</strong> This action cannot be undone</li>
      </ol>
    </section>

    <!-- Option Types -->
    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        Option Types Explained
      </h3>
      <div class="space-y-3">
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100">Text</h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">Single-line text input. Use for short strings, emails, names, etc.</p>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">Example: <code>app_name</code>, <code>support_email</code></p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100">Number</h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">Numeric value input. Use for counts, thresholds, percentages.</p>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">Example: <code>low_stock_threshold</code>, <code>max_form_slots</code></p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100">Textarea</h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">Multi-line text input. Use for descriptions, messages, content blocks.</p>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">Example: <code>app_description</code>, <code>footer_text</code></p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100">Boolean</h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">True/false toggle. Use for feature flags and yes/no settings.</p>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">Example: <code>enable_inventory_notifications</code>, <code>enable_api</code></p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100">Select</h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">Dropdown with predefined choices. Define options as JSON.</p>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">Example: <code>default_theme</code> with choices: light, dark, auto</p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100">Checkbox</h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">Boolean value. Similar to Boolean but displayed as checkbox.</p>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">Example: <code>agree_terms</code>, <code>is_active</code></p>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100">JSON</h4>
          <p class="text-sm text-gray-600 dark:text-gray-400">Complex data structure. Use for arrays, objects, and nested data.</p>
          <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">Example: <code>storage_locations</code>, <code>vehicles</code> array</p>
        </div>
      </div>
    </section>

    <!-- Search and Filter -->
    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        Using Search and Filters
      </h3>
      <p class="mb-3">The options index page provides several ways to find what you're looking for:</p>
      <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
        <li><strong>Search Box</strong> - Type to search by key, label, or description</li>
        <li><strong>Group Filter</strong> - Filter by category (system, email, inventory, etc.)</li>
        <li><strong>Type Filter</strong> - Filter by option type (text, select, json, etc.)</li>
        <li><strong>Pagination</strong> - Navigate through pages of 10 options each</li>
      </ul>
    </section>

    <!-- Using Options in Code -->
    <section v-if="showDeveloperSections">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        Using Options in Code
      </h3>
      <p class="mb-3">
        Developers can access options programmatically in the application code:
      </p>
      <div class="bg-gray-100 dark:bg-gray-800 p-4 rounded-lg text-xs font-mono space-y-3 overflow-x-auto">
        <div>
          <p class="text-gray-600 dark:text-gray-400 mb-2 font-sans">Get a single option:</p>
          <p class="text-gray-900 dark:text-gray-100">$appName = Option::getByKey('app_name');</p>
        </div>
        <div>
          <p class="text-gray-600 dark:text-gray-400 mb-2 font-sans">Get all options in a group:</p>
          <p class="text-gray-900 dark:text-gray-100">$emails = Option::getByGroup('email');</p>
        </div>
        <div>
          <p class="text-gray-600 dark:text-gray-400 mb-2 font-sans">Check a boolean option:</p>
          <p class="text-gray-900 dark:text-gray-100">if (Option::getByKey('enable_api') === 'true') { ... }</p>
        </div>
      </div>
    </section>

    <!-- Tips & Best Practices -->
    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        Tips & Best Practices
      </h3>
      <div class="space-y-2 text-gray-700 dark:text-gray-300">
        <p><strong>✓ Do:</strong></p>
        <ul class="list-disc list-inside ml-4 space-y-1">
          <li>Use descriptive labels and keys</li>
          <li>Add helpful descriptions for each option</li>
          <li>Organize related options into groups</li>
          <li>Use snake_case for key names</li>
          <li>Backup your database before bulk changes</li>
        </ul>
      </div>
      <div class="space-y-2 text-gray-700 dark:text-gray-300 mt-4">
        <p><strong>✗ Don't:</strong></p>
        <ul class="list-disc list-inside ml-4 space-y-1">
          <li>Store sensitive data (passwords, API keys)</li>
          <li>Use special characters in option keys</li>
          <li>Create options with identical keys</li>
          <li>Store extremely large data in JSON options</li>
        </ul>
      </div>
    </section>

    <!-- Need Help -->
    <section class="bg-blue-50 dark:bg-blue-900 p-6 rounded-lg border border-blue-200 dark:border-blue-700">
      <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-2">
        📚 Need More Help?
      </h3>
      <p class="text-blue-800 dark:text-blue-200 text-sm">
        For detailed technical documentation, visit the
        <strong>System Options Module</strong> documentation in the docs folder or ask your administrator.
      </p>
    </section>
  </div>
</template>

<script>
export default {
  props: {
    showDeveloperSections: {
      type: Boolean,
      default: true,
    },
  },
  name: 'SystemOptionsTopic',
};
</script>

<style scoped>
code {
  background-color: #f3f4f6;
  color: #1f2937;
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  font-family: 'Courier New', monospace;
}

:deep(.dark) code {
  background-color: #374151;
  color: #f3f4f6;
}
</style>
