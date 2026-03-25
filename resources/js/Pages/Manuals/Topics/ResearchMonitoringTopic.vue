<template>
    <div class="space-y-4 text-sm leading-7 text-gray-700 dark:text-gray-300">
        <p>
            The Research Monitoring module tracks projects, studies, experiments, and sample inventory in one workflow. Use
            barcode labels for audit/inventory movement and QR codes for retrieval, print payloads, and display context.
        </p>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Core Flow</h3>
        <ol class="list-decimal space-y-2 pl-6">
            <li>Create a project profile with commodity, budget, and leadership metadata.</li>
            <li>Add studies and define objective, supervisor, and staff members.</li>
            <li>Launch experiments, then register samples with generated UID barcodes.</li>
            <li>Capture stage-based monitoring records and mark records for export.</li>
            <li>Open Research Sample Inventory to scan/retrieve and print barcode/QR labels.</li>
        </ol>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Barcode And QR Usage</h3>
        <ul class="list-disc space-y-2 pl-6">
            <li>Barcode (UID) is optimized for audits, shelf checks, and quick inventory movement.</li>
            <li>QR payload contains sample retrieval context for mobile and presentation views.</li>
            <li>Label printing events and scan lookups are logged in sample inventory audit logs.</li>
        </ul>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Access Points</h3>
        <ul class="list-disc space-y-2 pl-6">
            <li>Research Dashboard: status overview and quick links.</li>
            <li>Projects and Experiments pages: data maintenance and monitoring updates.</li>
            <li>Research Sample Inventory: dedicated barcode/QR scanning and label printing.</li>
        </ul>
    </div>
</template>
