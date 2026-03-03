<template>
  <div class="space-y-6">
    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        Certificate Generator Manual
      </h3>
      <p>
        Use this guide when generating eCertificates from event responses in
        <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">/apps/event-forms</code>
        (Event Update page → eCertificates tab).
      </p>
    </section>

    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        Brief: Template Formatting Rules
      </h3>
      <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg p-4">
        <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
          <li>Use placeholder format <strong>&lt;&lt;COLUMN_NAME&gt;&gt;</strong> inside text boxes or table cells.</li>
          <li>Use the exact selected column key from the UI whenever possible (example: <code>fullname_8647</code>).</li>
          <li><strong>&lt;&lt;NAME&gt;&gt;</strong> can resolve to fullname-style fields, but exact keys are most reliable.</li>
          <li>Avoid putting placeholder brackets in separate text boxes; keep each placeholder in one text element.</li>
          <li>Save template as <strong>.pptx</strong> only.</li>
        </ul>
      </div>
    </section>

    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        Comprehensive: How to Use the Certificate Generator
      </h3>

      <ol class="list-decimal list-inside space-y-3 text-gray-700 dark:text-gray-300">
        <li>
          Open an event, then go to the <strong>eCertificates</strong> tab.
        </li>
        <li>
          Decide template source:
          <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
            <li>Enable <strong>Use saved template for this event</strong> to use the saved/default template.</li>
            <li>Disable it if you want to upload a new template now.</li>
            <li>Use <strong>View Saved Template</strong> to check the currently active template.</li>
          </ul>
        </li>
        <li>
          Decide data source:
          <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
            <li>Enable <strong>Use Event Responses (no Excel upload)</strong> to generate from submitted form data.</li>
            <li>Disable it to upload your own <strong>.xlsx</strong> or <strong>.csv</strong> file.</li>
          </ul>
        </li>
        <li>
          If using Event Responses:
          <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
            <li>Select <strong>Name column</strong>.</li>
            <li>Select <strong>Email column</strong>.</li>
            <li>Optionally select <strong>Subform type</strong> to limit the response source.</li>
          </ul>
        </li>
        <li>
          Set output format:
          <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
            <li><strong>PPTX</strong>: editable certificate files.</li>
            <li><strong>PDF</strong>: fixed-print format.</li>
            <li><strong>PNG/JPG</strong>: image exports (no Poppler setup needed in current generator flow).</li>
          </ul>
        </li>
        <li>
          Configure naming convention (optional):
          <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
            <li>Example: <code>{Fullname}_{date}</code></li>
            <li>You may use any column key in curly braces.</li>
          </ul>
        </li>
        <li>
          Click <strong>Upload &amp; Process</strong> and wait for status to become completed.
        </li>
        <li>
          Click <strong>Download ZIP</strong> to retrieve all generated files.
        </li>
      </ol>
    </section>

    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        Troubleshooting
      </h3>
      <div class="space-y-3">
        <div class="border-l-4 border-red-400 bg-red-50 dark:bg-red-900/20 p-3">
          <p><strong>Placeholder not replaced:</strong> use exact column keys from the Name/Email dropdown list, then regenerate.</p>
        </div>
        <div class="border-l-4 border-red-400 bg-red-50 dark:bg-red-900/20 p-3">
          <p><strong>413 Request too large:</strong> use saved template and event responses (no file upload), or reduce file size.</p>
        </div>
        <div class="border-l-4 border-red-400 bg-red-50 dark:bg-red-900/20 p-3">
          <p><strong>Conversion failure:</strong> verify LibreOffice path in environment and ensure the queue worker is restarted.</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
export default {
  name: 'CertificateGeneratorTopic',
}
</script>
