<template>
    <div class="space-y-6">
        <section>
            <h3 class="text-lg font-bold mb-3">Welcome to {{ $appName }}</h3>
            <p v-if="showDeveloperSections" class="mb-3">{{ $appName }} is a comprehensive resource management system designed to streamline operations for the center. This guide covers both <strong>administrative tasks</strong> (for end-users) and <strong>developer tasks</strong> (for technical staff).</p>
            <p v-else class="mb-3">{{ $appName }} is a comprehensive resource management system designed to streamline operations for the center. This public guide focuses on <strong>end-user workflows</strong>, common tasks, and where to go when you need help inside the system.</p>
        </section>

        <section>
            <h3 class="text-lg font-bold mb-3">Main Purpose</h3>
            <p class="mb-3">{{ $appName }} provides a centralized platform to:</p>
            <ul class="list-disc list-inside space-y-2 mb-4">
                <li>Manage events and participants</li>
                <li>Handle resource requests (facilities, equipment, supplies)</li>
                <li>Track and manage inventory</li>
                <li>Maintain user profiles and access control</li>
                <li>Audit system activities for compliance and security</li>
            </ul>
        </section>

        <section>
            <h3 class="text-lg font-bold mb-3">Core Modules & Features</h3>
            
            <div class="space-y-4">
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                    <h4 class="font-semibold mb-2">📋 Event Management</h4>
                    <p class="text-sm">Create and manage events with custom registration forms. Define requirements, track participants, and generate reports. Each event can have multiple form types for different purposes (registration, feedback, evaluations, etc.).</p>
                </div>

                <div class="bg-green-50 border-l-4 border-green-400 p-4">
                    <h4 class="font-semibold mb-2">📝 Custom Forms</h4>
                    <p class="text-sm">Build flexible, reusable forms for events. Event administrators can select from pre-defined form types or request developers to create specialized form types. Supports dynamic field configuration and validation rules.</p>
                </div>

                <div class="bg-purple-50 border-l-4 border-purple-400 p-4">
                    <h4 class="font-semibold mb-2">🏛️ FES Request System</h4>
                    <p class="text-sm">Facilities, Equipment, and Supplies request management. Users can submit requests for laboratory access, equipment usage, and supply procurement. Administrators review and approve/reject requests with full tracking and audit history.</p>
                </div>

                <div class="bg-amber-50 border-l-4 border-amber-400 p-4">
                    <h4 class="font-semibold mb-2">📦 Inventory Management</h4>
                    <p class="text-sm">Complete inventory tracking system including items, categories, suppliers, and transactions. Track incoming/outgoing stock movements, generate reports, monitor stock levels, and manage supplier information. Includes barcode support for efficient tracking.</p>
                </div>

                <div class="bg-orange-50 border-l-4 border-orange-400 p-4">
                    <h4 class="font-semibold mb-2">👤 User Management & Profiles</h4>
                    <p class="text-sm">Secure user account management with profile information, password management, two-factor authentication, and session control. Built on Laravel Fortify authentication framework.</p>
                </div>

                <div class="bg-red-50 border-l-4 border-red-400 p-4">
                    <h4 class="font-semibold mb-2">📊 Audit Logging</h4>
                    <p class="text-sm">Complete system audit trail tracking all user actions and system changes. Provides compliance documentation and security monitoring for system-wide activities.</p>
                </div>
            </div>
        </section>

        <section>
            <h3 class="text-lg font-bold mb-3">How to Use This Manual</h3>
            <ol class="list-decimal list-inside space-y-2">
                <li><strong>Select a topic</strong> from the left menu based on what you need to do</li>
                <li><strong>Look for your role:</strong> Most guides focus on operational steps first, then deeper implementation notes for signed-in staff</li>
                <li><strong>Follow the step-by-step instructions</strong> for the workflow you need</li>
                <li v-if="showDeveloperSections"><strong>Refer to code snippets and file paths</strong> when you need implementation details</li>
                <li><strong>Check the quick reference</strong> sections for common files and endpoints</li>
            </ol>
        </section>

        <section>
            <h3 class="text-lg font-bold mb-3">Quick Navigation</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded">
                    <p class="font-semibold text-sm">📝 Administrators</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">How to add forms, file requests, manage inventory</p>
                </div>
                <div v-if="showDeveloperSections" class="bg-gray-100 dark:bg-gray-700 p-3 rounded">
                    <p class="font-semibold text-sm">👨‍💻 Developers</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">API endpoints, models, validation, customization</p>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded">
                    <p class="font-semibold text-sm">🎯 Getting Started</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Custom forms, FES requests, and events</p>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded">
                    <p class="font-semibold text-sm">📚 Reference</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Inventory, supplier, item, and user management</p>
                </div>
            </div>
        </section>

        <section>
            <h3 class="text-lg font-bold mb-3">Technology Stack</h3>
            <p class="text-sm mb-2"><strong>Backend:</strong> Laravel 11, PHP, MySQL</p>
            <p class="text-sm mb-2"><strong>Frontend:</strong> Vue 3, TypeScript, Tailwind CSS</p>
            <p class="text-sm"><strong>Architecture:</strong> Component-based with Inertia.js, RESTful APIs, Domain-Driven Design patterns</p>
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
    name: 'OverviewTopic',
}
</script>
