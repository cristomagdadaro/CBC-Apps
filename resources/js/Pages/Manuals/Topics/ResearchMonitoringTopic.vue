<template>
    <div class="space-y-6">
        <section>
            <h3 class="text-lg font-bold mb-3">Overview</h3>
            <p class="mb-3">
                The <strong>Research Monitoring module</strong> tracks the full research workflow from project planning to
                experiments, monitoring records, and sample inventory. It is designed for teams that need a reliable chain of
                records for studies, samples, movement history, and printable identifiers.
            </p>
            <p>
                The module combines research metadata, barcode-based inventory handling, QR-based retrieval context, and audit
                visibility so staff can trace what was created, where it is stored, and what monitoring stages have already been completed.
            </p>
        </section>

        <section>
            <h3 class="text-lg font-bold mb-3">For Non-Programmers (Researchers, Staff, and Supervisors)</h3>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">What you can do in this module</h4>
                <ul class="list-disc list-inside space-y-2 text-sm">
                    <li>Create and maintain research project profiles with funding, duration, and leadership details</li>
                    <li>Add studies and experiments under a single project for organized monitoring</li>
                    <li>Register samples and assign unique barcode identifiers for storage and movement tracking</li>
                    <li>Record monitoring updates across research stages</li>
                    <li>Scan, retrieve, and print labels from the sample inventory view</li>
                </ul>
            </div>

            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Recommended workflow</h4>
                <ol class="list-decimal list-inside space-y-2 text-sm">
                    <li>Create a <strong>Research Project</strong> and fill in title, commodity, dates, budget, and project leader</li>
                    <li>Add the related <strong>Study or Experiment</strong> records so the project is broken into manageable activities</li>
                    <li>Register samples once materials are available for tracking</li>
                    <li>Generate and print barcode or QR labels before storage or distribution</li>
                    <li>Use monitoring records to log progress, checkpoints, observations, and completion status</li>
                    <li>Open <strong>Research Sample Inventory</strong> whenever you need to scan, retrieve, inspect, or print labels again</li>
                </ol>
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Barcode and QR labels</h4>
                <ul class="list-disc list-inside space-y-2 text-sm">
                    <li><strong>Barcode</strong> is best for quick scanning during audits, shelf checks, hand-offs, and inventory movement</li>
                    <li><strong>QR code</strong> is best when the scanner or viewer needs more context such as retrieval details or display-ready sample data</li>
                    <li>Label printing and scan-based lookups should be treated as part of the sample history, so staff should avoid reusing labels across different samples</li>
                </ul>
            </div>

            <div class="bg-purple-50 border-l-4 border-purple-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Main access points</h4>
                <ul class="list-disc list-inside space-y-2 text-sm">
                    <li><strong>Research Dashboard</strong> for overview cards and quick navigation</li>
                    <li><strong>Research Projects</strong> for the main project profile and nested research activities</li>
                    <li><strong>Research Experiments</strong> for experiment-level monitoring and status work</li>
                    <li><strong>Research Sample Inventory</strong> for barcode scanning, sample retrieval, and label printing</li>
                </ul>
            </div>

            <div class="bg-amber-50 border-l-4 border-amber-400 p-4">
                <h4 class="font-semibold mb-2">Good operating practices</h4>
                <ul class="list-disc list-inside space-y-2 text-sm">
                    <li>Finalize project and experiment titles before printing large batches of labels</li>
                    <li>Always verify the sample before reprinting or replacing a barcode</li>
                    <li>Use consistent storage naming so scanned records match the physical storage location</li>
                    <li>Capture monitoring updates on time to keep reports and dashboards accurate</li>
                </ul>
            </div>
        </section>

        <section v-if="showDeveloperSections">
            <h3 class="text-lg font-bold mb-3">For Programmers (Architecture and Implementation)</h3>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Implementation focus</h4>
                <p class="text-sm mb-2">
                    The module is split into project management, experiment monitoring, and sample inventory concerns. The frontend is Inertia and Vue driven, while Laravel controllers provide data for pages and repository-backed workflows.
                </p>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li>Project-level pages define the top-level research structure</li>
                    <li>Experiment pages hold workflow state, stage updates, and execution context</li>
                    <li>Sample inventory pages handle barcode and QR based retrieval and printing flows</li>
                </ul>
            </div>

            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Key user-facing pages and likely touch points</h4>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li><code class="bg-gray-100 px-1">resources/js/Pages/Research/ResearchDashboard.vue</code> – Research dashboard and overview</li>
                    <li><code class="bg-gray-100 px-1">resources/js/Pages/Research/Projects/ResearchProjectIndex.vue</code> – Project listing</li>
                    <li><code class="bg-gray-100 px-1">resources/js/Pages/Research/Projects/ResearchProjectCreate.vue</code> – Project creation flow</li>
                    <li><code class="bg-gray-100 px-1">resources/js/Pages/Research/Projects/ResearchProjectShow.vue</code> – Project detail and nested activity view</li>
                    <li><code class="bg-gray-100 px-1">resources/js/Pages/Research/Experiments/ResearchExperimentShow.vue</code> – Experiment monitoring detail page</li>
                    <li><code class="bg-gray-100 px-1">resources/js/Pages/Research/Samples/ResearchSampleInventory.vue</code> – Barcode, QR, and retrieval inventory view</li>
                </ul>
            </div>

            <div class="bg-purple-50 border-l-4 border-purple-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Backend routing and orchestration</h4>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li><code class="bg-gray-100 px-1">app/Http/Controllers/Research/ResearchPageController.php</code> provides the primary Inertia pages for the module</li>
                    <li><code class="bg-gray-100 px-1">routes/research.php</code> defines research-specific routes and endpoints</li>
                    <li>Any new workflow should preserve the existing controller-to-service or repository flow rather than adding page logic directly into Vue</li>
                </ul>
            </div>

            <div class="bg-pink-50 border-l-4 border-pink-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Barcode and QR design notes</h4>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li>Barcode payloads should stay compact and stable so scanners can read them consistently during inventory operations</li>
                    <li>QR payloads can include richer retrieval context, but should still avoid storing unnecessary presentation-only data</li>
                    <li>Printing flows should use a single source of truth for sample identity to prevent mismatch between visible labels and saved records</li>
                    <li>Audit logs are especially important for label regeneration and scan-triggered retrieval actions</li>
                </ul>
            </div>

            <div class="bg-orange-50 border-l-4 border-orange-400 p-4">
                <h4 class="font-semibold mb-2">When extending the module</h4>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li>Keep project, experiment, and sample inventory responsibilities separate</li>
                    <li>Prefer repository or service changes over embedding data orchestration inside page components</li>
                    <li>Add tests when introducing new monitoring stages, sample retrieval rules, or label-generation logic</li>
                    <li>Preserve barcode and QR compatibility when changing label payload structure</li>
                </ul>
            </div>
        </section>
    </div>
</template>

<script>
export default {
    name: 'ResearchMonitoringTopic',
    props: {
        showDeveloperSections: {
            type: Boolean,
            default: true,
        },
    },
}
</script>
