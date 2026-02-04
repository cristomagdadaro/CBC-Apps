<template>
    <div class="space-y-6">
        <section>
            <h3 class="text-lg font-bold mb-3">For Non-Programmers (Event Administrators)</h3>
            <p class="mb-3">If you only need a <strong>new form for a specific event</strong>, you do not need code changes. Use the Requirements manager when creating or editing an event form.</p>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Steps:</h4>
                <ol class="list-decimal list-inside space-y-2">
                    <li>Go to the event form create/edit page</li>
                    <li>In the <strong>Requirements</strong> section, click <strong>Add form</strong></li>
                    <li>Choose a <strong>Form type</strong> from the dropdown (e.g., Registration, Feedback)</li>
                    <li>Configure optional settings:
                        <ul class="list-disc list-inside ml-4 mt-2">
                            <li>Required vs. optional</li>
                            <li>Open/close dates</li>
                            <li>Max slots</li>
                            <li>Limits (per region, city, organization, etc.)</li>
                        </ul>
                    </li>
                    <li>Save the event form</li>
                </ol>
            </div>
            <p class="text-sm text-gray-600"><strong>Note:</strong> If the dropdown does not have the form type you need, ask a developer to create a new custom form type.</p>
        </section>

        <hr class="my-6">

        <section>
            <h3 class="text-lg font-bold mb-3">For Programmers (Adding a Brand-New Form Type)</h3>
            <p class="mb-3">A <strong>custom form type</strong> is identified by a unique slug (e.g., <code class="bg-gray-100 px-2 py-1 rounded">speaker_evaluation</code>). Adding a new type requires changes in both backend validation and frontend UI.</p>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Step 1: Choose the new form type slug</h4>
                <p>Pick a short, lowercase, underscore-separated slug, e.g. <code class="bg-gray-100 px-2 py-1 rounded">speaker_evaluation</code>. You will use this slug in all steps below.</p>
            </div>

            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Step 2: Backend – Register the new form type</h4>
                <ul class="list-disc list-inside space-y-2">
                    <li><strong>Add the new enum case:</strong> Update <code class="bg-gray-100 px-2 py-1 rounded">app/Enums/Subform.php</code></li>
                    <li><strong>Add validation rules:</strong> Update <code class="bg-gray-100 px-2 py-1 rounded">config/subformtypes.php</code></li>
                    <li><strong>Update participant requirements (if needed):</strong> Update <code class="bg-gray-100 px-2 py-1 rounded">app/Http/Requests/CreateEventSubformResponseRequest.php</code></li>
                </ul>
            </div>

            <div class="bg-purple-50 border-l-4 border-purple-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Step 3: Frontend – Add default field structure</h4>
                <p class="mb-2">Define the default field values used by the UI when the form loads:</p>
                <p>Update <code class="bg-gray-100 px-2 py-1 rounded">resources/js/Modules/dto/DtoSubformResponse.ts</code></p>
                <p class="text-sm mt-2">Add a new <code class="bg-gray-100 px-2 py-1 rounded">case</code> in <code class="bg-gray-100 px-2 py-1 rounded">getSubformFields()</code> that returns your new fields.</p>
            </div>

            <div class="bg-pink-50 border-l-4 border-pink-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Step 4: Frontend – Create the form component</h4>
                <p class="mb-2">Create a new Vue component that renders the inputs and submission logic.</p>
                <p class="text-sm">Location: <code class="bg-gray-100 px-2 py-1 rounded">resources/js/Pages/Forms/components</code></p>
                <p class="text-sm mt-2"><strong>Examples to follow:</strong></p>
                <ul class="list-disc list-inside text-sm mt-2 ml-2">
                    <li><code class="bg-gray-100 px-2 py-1 rounded">RegistrationCard.vue</code></li>
                    <li><code class="bg-gray-100 px-2 py-1 rounded">FeedbackCard.vue</code></li>
                </ul>
            </div>

            <div class="bg-indigo-50 border-l-4 border-indigo-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Step 5: Frontend – Wire into the guest workflow</h4>
                <p class="mb-2">To show the new form in the public/guest flow:</p>
                <ul class="list-disc list-inside space-y-2 text-sm">
                    <li>Add the component import and registration in <code class="bg-gray-100 px-2 py-1 rounded">resources/js/Pages/Forms/components/GuestCard.vue</code></li>
                    <li>Add a label for the new tab in the <code class="bg-gray-100 px-2 py-1 rounded">labelMap</code></li>
                    <li>Add a <code class="bg-gray-100 px-2 py-1 rounded">v-if</code> block for the new form type in the tab content</li>
                </ul>
                <p class="text-sm mt-2"><strong>Note:</strong> If the form should not be available in the guest UI, you can skip this step.</p>
            </div>

            <div class="bg-orange-50 border-l-4 border-orange-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Step 6: Frontend – Add to Requirements dropdown</h4>
                <p class="mb-2">To make the form type selectable when creating/editing events:</p>
                <p class="text-sm">Update <code class="bg-gray-100 px-2 py-1 rounded">resources/js/Components/Forms/RequirementsManager.vue</code></p>
                <p class="text-sm mt-2">Add a <code class="bg-gray-100 px-2 py-1 rounded">{ value, label }</code> entry to <code class="bg-gray-100 px-2 py-1 rounded">formTypeOptions</code>.</p>
            </div>

            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                <h4 class="font-semibold mb-2">Step 7: Seeders/Tests (Optional)</h4>
                <p class="text-sm">If tests or factories rely on a full list of form types, update them:</p>
                <ul class="list-disc list-inside text-sm mt-2 ml-2">
                    <li><code class="bg-gray-100 px-2 py-1 rounded">database/factories/EventSubformResponseFactory.php</code></li>
                    <li>Any workflow or feature tests</li>
                </ul>
            </div>
        </section>

        <section class="mt-6">
            <h3 class="text-lg font-bold mb-3">Quick Checklist</h3>
            <div class="bg-gray-50 p-4 rounded">
                <ul class="space-y-2 text-sm">
                    <li>☐ New enum value added</li>
                    <li>☐ New config validation rules added</li>
                    <li>☐ Participant requirement updated (if needed)</li>
                    <li>☐ New UI defaults added in <code class="bg-gray-100 px-1">getSubformFields()</code></li>
                    <li>☐ New form component created</li>
                    <li>☐ Guest UI updated (if applicable)</li>
                    <li>☐ Requirements dropdown updated</li>
                    <li>☐ Tests/factories updated</li>
                </ul>
            </div>
        </section>

        <section class="mt-6">
            <h3 class="text-lg font-bold mb-3">Common Pitfalls</h3>
            <div class="space-y-3">
                <div class="border-l-4 border-red-400 bg-red-50 p-3">
                    <p><strong>Form type not in dropdown:</strong> Add it to <code class="bg-gray-100 px-2 py-1 rounded">formTypeOptions</code>.</p>
                </div>
                <div class="border-l-4 border-red-400 bg-red-50 p-3">
                    <p><strong>Validation fails on submit:</strong> Ensure your field names match in both frontend defaults and backend config.</p>
                </div>
                <div class="border-l-4 border-red-400 bg-red-50 p-3">
                    <p><strong>Guest UI shows "not yet supported":</strong> Add the new tab mapping and component in the guest workflow.</p>
                </div>
            </div>
        </section>
    </div>
</template>

<script>
export default {
    name: 'CustomFormTopic',
}
</script>
