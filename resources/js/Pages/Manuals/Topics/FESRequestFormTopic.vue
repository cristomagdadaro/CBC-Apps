<template>
    <div class="space-y-6">
        <section>
            <h3 class="text-lg font-bold mb-3">For Non-Programmers (Requestors & Administrators)</h3>
            <p class="mb-3">The <strong>Facilities, Equipment, and Supplies (FES) Request Form</strong> allows researchers, students, and staff to request access to laboratory facilities, equipment, and supplies.</p>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">How to Submit a Request (Non-Programmer):</h4>
                <ol class="list-decimal list-inside space-y-2 text-sm">
                    <li><strong>Access the form:</strong> Navigate to FES Request Form or use the public link</li>
                    <li><strong>Fill requestor information:</strong>
                        <ul class="list-disc list-inside ml-4 mt-2">
                            <li>Full name</li>
                            <li>Affiliation / Organization</li>
                            <li>Position / Role</li>
                            <li>Email address</li>
                            <li>Phone number</li>
                        </ul>
                    </li>
                    <li><strong>Select request type(s):</strong> Check all that apply:
                        <ul class="list-disc list-inside ml-4 mt-2">
                            <li>Supplies</li>
                            <li>Equipment</li>
                            <li>Laboratory Access</li>
                        </ul>
                    </li>
                    <li><strong>Provide request details:</strong>
                        <ul class="list-disc list-inside ml-4 mt-2">
                            <li>Purpose of request</li>
                            <li>Project title</li>
                            <li>Special instructions or requirements</li>
                        </ul>
                    </li>
                    <li><strong>Specify items needed:</strong>
                        <ul class="list-disc list-inside ml-4 mt-2">
                            <li>List supplies required (if applicable)</li>
                            <li>List equipment needed (if applicable)</li>
                            <li>List laboratory facilities required (if applicable)</li>
                        </ul>
                    </li>
                    <li><strong>Set date and time:</strong> Specify when you need the resources</li>
                    <li><strong>Accept terms & conditions:</strong> Read and agree to all clauses</li>
                    <li><strong>Submit:</strong> Click submit to send your request</li>
                </ol>
            </div>

            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Request Status & Tracking:</h4>
                <ul class="list-disc list-inside space-y-2 text-sm">
                    <li><strong>Pending:</strong> Your request has been submitted and is awaiting review</li>
                    <li><strong>Approved:</strong> Your request has been approved. You can now use the requested resources</li>
                    <li><strong>Rejected:</strong> Your request was not approved. Contact the administrator for details</li>
                </ul>
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Administrator/Approver Workflow:</h4>
                <ol class="list-decimal list-inside space-y-2 text-sm">
                    <li>Navigate to <strong>FES Request Form</strong> section in the admin panel</li>
                    <li>View all pending requests</li>
                    <li>Click on a request to review details</li>
                    <li>Approve or reject the request</li>
                    <li>If approved, add any notes or special instructions</li>
                    <li>Requestor will receive confirmation</li>
                </ol>
            </div>
        </section>

        <hr class="my-6">

        <section v-if="showDeveloperSections">
            <h3 class="text-lg font-bold mb-3">For Programmers (Form Customization & Integration)</h3>
            <p class="mb-3">The FES Request Form is built on a flexible form system. Modifications require changes to both the request model/validation and the Vue component.</p>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Form Structure Overview:</h4>
                <p class="text-sm mb-2"><strong>Database Model:</strong> <code class="bg-gray-100 px-2 py-1 rounded">App\\Models\\RequestFormPivot</code> (submission records)</p>
                <p class="text-sm mb-2"><strong>Validation Request:</strong> <code class="bg-gray-100 px-2 py-1 rounded">App\\Http\\Requests\\CreateLabRequest</code></p>
                <p class="text-sm mb-2"><strong>Frontend Component:</strong> <code class="bg-gray-100 px-2 py-1 rounded">resources/js/Pages/LabRequest/components/RequesterGuestCard.vue</code></p>
                <p class="text-sm"><strong>DTO:</strong> <code class="bg-gray-100 px-2 py-1 rounded">RequestFormPivot.ts</code> and <code class="bg-gray-100 px-2 py-1 rounded">DtoRequestForm.ts</code></p>
            </div>

            <div class="bg-purple-50 border-l-4 border-purple-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Step 1: Add a new field to the form</h4>
                <p class="text-sm mb-2">If you need to add a new field to the request form:</p>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li>Update <code class="bg-gray-100 px-2 py-1 rounded">app/Http/Requests/CreateLabRequest.php</code> to add validation rules</li>
                    <li>Update <code class="bg-gray-100 px-2 py-1 rounded">resources/js/Modules/dto/DtoRequestForm.ts</code> to add field defaults</li>
                    <li>Add the input component to <code class="bg-gray-100 px-2 py-1 rounded">resources/js/Pages/LabRequest/components/RequesterGuestCard.vue</code></li>
                </ul>
            </div>

            <div class="bg-pink-50 border-l-4 border-pink-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Step 2: Modify request types</h4>
                <p class="text-sm mb-2">To change available request types (Supplies, Equipment, Laboratory Access):</p>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li>Update <code class="bg-gray-100 px-2 py-1 rounded">requestTypeOptions</code> array in <code class="bg-gray-100 px-2 py-1 rounded">RequesterGuestCard.vue</code></li>
                    <li>Update validation in <code class="bg-gray-100 px-2 py-1 rounded">CreateLabRequest.php</code>: <code class="bg-gray-100 px-2 py-1 rounded">'string|in:...'</code></li>
                </ul>
            </div>

            <div class="bg-orange-50 border-l-4 border-orange-400 p-4 mb-4">
                <h4 class="font-semibold mb-2">Step 3: Create approval workflows</h4>
                <p class="text-sm mb-2">To customize the approval process:</p>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li>Update <code class="bg-gray-100 px-2 py-1 rounded">app/Http/Requests/UpdateRequestFormPivot.php</code></li>
                    <li>Modify the approval form component <code class="bg-gray-100 px-2 py-1 rounded">UseRequestApprovalBtn.vue</code></li>
                    <li>Update the approval logic in <code class="bg-gray-100 px-2 py-1 rounded">RequestFormPivotController.php</code></li>
                </ul>
            </div>

            <div class="bg-red-50 border-l-4 border-red-400 p-4">
                <h4 class="font-semibold mb-2">Step 4: Modify terms & conditions clauses</h4>
                <p class="text-sm mb-2">To update the agreement clauses:</p>
                <ul class="list-disc list-inside space-y-1 text-sm">
                    <li>Update clause labels and descriptions in <code class="bg-gray-100 px-2 py-1 rounded">RequesterGuestCard.vue</code></li>
                    <li>Add/remove clause validation fields in both request classes</li>
                    <li>Update database migrations if adding/removing clause columns</li>
                </ul>
            </div>
        </section>

        <section class="mt-6">
            <h3 class="text-lg font-bold mb-3">Quick Reference: Key Files</h3>
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded space-y-2 text-sm">
                <p><strong>Backend:</strong></p>
                <ul class="list-disc list-inside ml-2 space-y-1">
                    <li><code class="bg-gray-100 dark:bg-gray-600 px-1">app/Models/RequestFormPivot.php</code> – Request submission model</li>
                    <li><code class="bg-gray-100 dark:bg-gray-600 px-1">app/Models/UseRequestForm.php</code> – Request form template</li>
                    <li><code class="bg-gray-100 dark:bg-gray-600 px-1">app/Http/Requests/CreateLabRequest.php</code> – Submission validation</li>
                    <li><code class="bg-gray-100 dark:bg-gray-600 px-1">app/Http/Controllers/LabRequestFormController.php</code> – Request form routing</li>
                </ul>
                <p class="mt-3"><strong>Frontend:</strong></p>
                <ul class="list-disc list-inside ml-2 space-y-1">
                    <li><code class="bg-gray-100 dark:bg-gray-600 px-1">resources/js/Pages/LabRequest/UseRequestFormGuest.vue</code> – Main page</li>
                    <li><code class="bg-gray-100 dark:bg-gray-600 px-1">resources/js/Pages/LabRequest/components/RequesterGuestCard.vue</code> – Request form</li>
                    <li><code class="bg-gray-100 dark:bg-gray-600 px-1">resources/js/Pages/LabRequest/components/UseRequestCard.vue</code> – Request display</li>
                    <li><code class="bg-gray-100 dark:bg-gray-600 px-1">resources/js/Modules/domain/RequestFormPivot.ts</code> – DTO class</li>
                </ul>
            </div>
        </section>

        <section class="mt-6">
            <h3 class="text-lg font-bold mb-3">Common Tasks</h3>
            <div class="space-y-3">
                <div class="border-l-4 border-blue-400 bg-blue-50 p-3">
                    <p><strong>Add a new request type:</strong> Update validation and dropdown options in request handling files.</p>
                </div>
                <div class="border-l-4 border-blue-400 bg-blue-50 p-3">
                    <p><strong>Change approval workflow:</strong> Modify <code class="bg-gray-100 px-1">UpdateRequestFormPivot.php</code> and approval component.</p>
                </div>
                <div class="border-l-4 border-blue-400 bg-blue-50 p-3">
                    <p><strong>Export requests as PDF:</strong> Located in <code class="bg-gray-100 px-1">resources/views/generator/pdf/printable-request-form.blade.php</code></p>
                </div>
                <div class="border-l-4 border-blue-400 bg-blue-50 p-3">
                    <p><strong>Modify multi-step form layout:</strong> Edit the <code class="bg-gray-100 px-1">steps</code> array in RequesterGuestCard.vue.</p>
                </div>
            </div>
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
    name: 'FESRequestFormTopic',
}
</script>
