<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import SuccessModal from "@/Components/SuccessModal.vue";
import PersonnelRegistration from "@/Modules/domain/PersonnelRegistration";
import DtoResponse from "@/Modules/dto/DtoResponse";
import DtoError from "@/Modules/dto/DtoError";

export default {
    name: "PersonnelRegistrationGuest",
    components: { SuccessModal },
    mixins: [ApiMixin],
    data() {
        return {
            submitted: false,
            submittedEmail: null,
            delayReady: false,
            showSubmittedModal: false,
        };
    },
    computed: {
        isPhilRiceEmployee() {
            return this.form?.is_philrice_employee !== false;
        },
    },
    beforeMount() {
        this.model = new PersonnelRegistration();
        this.setFormAction("create");
        this.form.is_philrice_employee = true;
    },
    mounted() {
        setTimeout(() => {
            this.delayReady = true;
        }, 150);
    },
    methods: {
        async submitRegistration() {
            const response = await this.submitCreate(false);

            if (response instanceof DtoResponse) {
                this.submittedEmail = response.data?.data?.email ?? this.form.email;
                this.submitted = true;
                this.showSubmittedModal = true;
            }

            if (response instanceof DtoError) {
                this.submitted = false;
                this.showSubmittedModal = false;
            }
        },
    },
};
</script>

<template>
    <Head title="Personnel Registration" />

    <guest-form-page
        title="Personnel Registration"
        subtitle="Register your personnel details and verify your email before administrator approval."
        :delay-ready="delayReady"
        guide-key="personnel-registration-guest"
        max-width="max-w-3xl"
    >
        <transition-container v-show="delayReady" :duration="800" type="slide-bottom">
            <div class="bg-white rounded-lg shadow-lg border border-gray-100 p-5">
                <div v-if="submitted" class="rounded-lg border border-green-200 bg-green-50 p-5 text-green-900">
                    <h2 class="font-bold text-lg leading-tight">Check your email</h2>
                    <p class="mt-2 text-sm">
                        We sent a verification link to {{ submittedEmail }}. Check your Gmail inbox and confirm your email address so your registration can proceed to administrator review.
                    </p>
                    <Link href="/" class="inline-flex mt-4 rounded bg-AB px-4 py-2 text-sm font-semibold text-white">
                        Return to services
                    </Link>
                </div>

                <form v-else-if="form" data-guide="personnel-registration-form" @submit.prevent="submitRegistration" class="flex flex-col gap-4">
                    <div>
                        <h2 class="font-bold uppercase leading-none">Personnel Information</h2>
                        <p class="text-sm text-gray-600 mt-1">Use your active email address. Verification is required before approval.</p>
                    </div>

                    <div class="grid md:grid-cols-4 gap-2">
                        <text-input required label="First Name" v-model="form.fname" :error="form.errors.fname" autocomplete="given-name" />
                        <text-input label="Middle Name" v-model="form.mname" :error="form.errors.mname" autocomplete="additional-name" />
                        <text-input required label="Last Name" v-model="form.lname" :error="form.errors.lname" autocomplete="family-name" />
                        <text-input label="Suffix" v-model="form.suffix" :error="form.errors.suffix" autocomplete="honorific-suffix" />
                    </div>

                    <div class="grid md:grid-cols-2 gap-2">
                        <div class="space-y-1">
                            <label class="text-sm font-medium text-gray-700">Personnel Type</label>
                            <select v-model="form.is_philrice_employee" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-AB focus:ring-AB">
                                <option :value="true">PhilRice Employee</option>
                                <option :value="false">OJT / Thesis / Outsider</option>
                            </select>
                        </div>
                        <text-input
                            v-if="isPhilRiceEmployee"
                            required
                            label="PhilRice ID"
                            v-model="form.employee_id"
                            :error="form.errors.employee_id"
                        />
                        <div v-else class="rounded-lg border border-blue-100 bg-blue-50 p-3 text-sm text-blue-900">
                            CBC ID will be assigned after approval.
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-2">
                        <text-input required label="Year Level / Position / Role" v-model="form.position" :error="form.errors.position" autocomplete="organization-title" />
                        <text-input required type="email" label="Email" v-model="form.email" :error="form.errors.email" autocomplete="email" />
                        <text-input label="Phone" v-model="form.phone" :error="form.errors.phone" autocomplete="tel" />
                        <text-input label="Address" v-model="form.address" :error="form.errors.address" autocomplete="street-address" />
                    </div>

                    <div class="flex justify-end">
                        <submit-btn :disabled="processing || model.api.processing">
                            <span v-if="processing || model.api.processing">Submitting</span>
                            <span v-else>Submit Registration</span>
                        </submit-btn>
                    </div>
                </form>
            </div>
        </transition-container>

        <success-modal
            :show="showSubmittedModal"
            title="Registration Submitted"
            @close="showSubmittedModal = false"
        >
            <template #content>
                <p>
                    Your personnel registration has been submitted.
                </p>
                <p class="mt-2">
                    Check your Gmail inbox for the confirmation link sent to <span class="font-semibold">{{ submittedEmail }}</span>, then confirm your email address to continue.
                </p>
            </template>
            <template #footer>
                <div class="flex w-full justify-end gap-2">
                    <button
                        type="button"
                        class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                        @click="showSubmittedModal = false"
                    >
                        Close
                    </button>
                    <Link
                        href="/"
                        class="inline-flex justify-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700"
                    >
                        Return to services
                    </Link>
                </div>
            </template>
        </success-modal>
    </guest-form-page>
</template>
