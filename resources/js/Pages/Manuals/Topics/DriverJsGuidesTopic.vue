<template>
  <div class="space-y-6">
    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        Interactive Tours with Driver.js
      </h3>
      <p>
        CBC-Apps uses Driver.js to provide interactive tours for the landing page and
        public-facing workflows. These tours help first-time guests understand where to
        start, what information is collected, and how to replay or disable the guide.
      </p>
    </section>

    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        Current Guest Flow
      </h3>
      <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-300">
        <li>Guests land on the public welcome page.</li>
        <li>They are shown a data privacy notice before the startup guide is enabled.</li>
        <li>If they agree and guides are enabled, the startup tour runs automatically for first-time visitors.</li>
        <li>Every public page exposes guide controls so the tour can be replayed or automatic guides can be toggled.</li>
      </ol>
    </section>

    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        Files You Will Touch
      </h3>
      <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="font-semibold text-gray-900 dark:text-gray-100">Guide Registry</p>
          <p class="mt-1"><code>resources/js/Modules/guides/tourRegistry.js</code></p>
          <p class="mt-1">Defines guide keys, steps, inheritance, and the local-storage keys used to remember guide state.</p>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="font-semibold text-gray-900 dark:text-gray-100">Guide Runtime</p>
          <p class="mt-1"><code>resources/js/Modules/composables/useGuideTour.js</code></p>
          <p class="mt-1">Starts a Driver.js tour, respects privacy consent, tracks automatic-guide preferences, and marks tours as seen.</p>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="font-semibold text-gray-900 dark:text-gray-100">Guest Controls</p>
          <p class="mt-1"><code>resources/js/Components/GuideTourControls.vue</code></p>
          <p class="mt-1">Renders the floating controls used to start a guide or toggle automatic tours.</p>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
          <p class="font-semibold text-gray-900 dark:text-gray-100">Privacy Notice</p>
          <p class="mt-1"><code>resources/js/Components/DataPrivacyNoticeModal.vue</code></p>
          <p class="mt-1">Captures the guest acknowledgement required before startup guides run automatically.</p>
        </div>
      </div>
    </section>

    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        How to Add a Tour to a New Public Feature
      </h3>
      <ol class="list-decimal list-inside space-y-2 text-gray-700 dark:text-gray-300">
        <li>Create a stable guide key in <code>tourRegistry.js</code>.</li>
        <li>Add 2-4 short steps that explain the page purpose, the primary action area, and the guide controls.</li>
        <li>Mark the important page regions with <code>data-guide</code> attributes so the tour can anchor reliably.</li>
        <li>If the page uses <code>GuestFormPage</code>, pass a <code>guide-key</code> prop and reuse the shared guest-page tour.</li>
        <li>If the page is not based on <code>GuestFormPage</code>, render <code>GuideTourControls</code> directly and add its own guide anchors.</li>
        <li>Update the manuals topic if the new tour introduces a new public workflow or implementation pattern.</li>
      </ol>
    </section>

    <section>
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        Implementation Notes
      </h3>
      <ul class="list-disc list-inside space-y-2 text-gray-700 dark:text-gray-300">
        <li>Keep selectors stable and semantic. Prefer <code>data-guide</code> attributes over styling classes.</li>
        <li>Keep copy brief and task-focused. Tours should orient, not duplicate the manuals.</li>
        <li>Public tours should avoid exposing internal-only details, private names, or staff-only workflow rules.</li>
        <li>When a public page collects data, ensure the privacy notice and the tour language match the actual data use.</li>
      </ul>
    </section>
  </div>
</template>

<script>
export default {
  name: "DriverJsGuidesTopic",
  props: {
    showDeveloperSections: {
      type: Boolean,
      default: true,
    },
  },
};
</script>
