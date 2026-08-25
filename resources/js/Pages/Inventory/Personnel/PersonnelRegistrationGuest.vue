<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import SuccessModal from "@/Components/SuccessModal.vue";
import ImageCropperModal from "@/Components/ImageCropperModal.vue";
import PersonnelRegistration from "@/Modules/domain/PersonnelRegistration";
import DtoResponse from "@/Modules/dto/DtoResponse";
import DtoError from "@/Modules/dto/DtoError";
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import ProgressTabs from "@/Components/ProgressTabs.vue";

export default {
    name: "PersonnelRegistrationGuest",
    components: { SuccessModal, ImageCropperModal, TextInput, InputError, ProgressTabs },
    mixins: [ApiMixin],
    data() {
        return {
            currentStep: 1,
            submitted: false,
            submittedEmail: null,
            delayReady: false,
            showSubmittedModal: false,
            showCropModal: false,
            rawImageUrl: null,
            photoPreviewUrl: null,
            registrationTypeOptions: [
                { value: "student", label: "Student" },
                { value: "ojt", label: "OJT" },
                { value: "thesis", label: "Thesis" },
            ],
            stepErrors: {},
        };
    },
    computed: {
        isPhilRiceEmployee() {
            return this.form?.is_philrice_employee === true;
        },
        requiresCbcIdCard() {
            return this.form?.is_philrice_employee === false;
        },
    },
    beforeMount() {
        this.model = new PersonnelRegistration();
        this.setFormAction("create");
        this.form.is_philrice_employee = null;
        this.form.registration_type = null;
    },
    mounted() {
        setTimeout(() => {
            this.delayReady = true;
        }, 150);
    },
    methods: {
        setCategory(isPhilRice) {
            this.form.is_philrice_employee = isPhilRice;
            this.form.registration_type = isPhilRice ? "philrice_employee" : "student";
            this.nextStep();
        },
        validateStep2() {
            this.stepErrors = {};
            let valid = true;
            if (!this.form.fname) {
                this.stepErrors.fname = "First name is required.";
                valid = false;
            }
            if (!this.form.lname) {
                this.stepErrors.lname = "Last name is required.";
                valid = false;
            }
            if (!this.form.email) {
                this.stepErrors.email = "Email is required.";
                valid = false;
            }

            if (this.form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
                this.stepErrors.email = "Valid email is required.";
                valid = false;
            }

            if (valid) {
                this.nextStep();
            }
        },
        nextStep() {
            if (this.currentStep < 3) this.currentStep++;
        },
        prevStep() {
            if (this.currentStep > 1) this.currentStep--;
        },
        async submitRegistration() {
            this.stepErrors = {};
            this.syncRegistrationType();

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
        onPhotoSelected(event) {
            const file = event.target.files?.[0] ?? null;
            if (!file) return;

            if (this.rawImageUrl) {
                URL.revokeObjectURL(this.rawImageUrl);
            }

            this.rawImageUrl = URL.createObjectURL(file);
            this.showCropModal = true;

            event.target.value = "";
        },
        onPhotoCropped(blob) {
            const file = new File([blob], "id_photo.jpg", { type: blob.type });
            this.form.id_photo = file;

            if (this.photoPreviewUrl) {
                URL.revokeObjectURL(this.photoPreviewUrl);
            }

            this.photoPreviewUrl = URL.createObjectURL(blob);
        },
        syncRegistrationType() {
            if (this.isPhilRiceEmployee) {
                this.form.registration_type = "philrice_employee";
                this.form.course_program = null;
                this.form.affiliation = null;
                this.form.expires_at = null;
                this.form.id_photo = null;
                if (this.photoPreviewUrl) {
                    URL.revokeObjectURL(this.photoPreviewUrl);
                    this.photoPreviewUrl = null;
                }
            } else {
                this.form.employee_id = null;
            }
        },
    },
    watch: {
        "form.is_philrice_employee": {
            handler() {
                if (this.form) {
                    this.syncRegistrationType();
                }
            },
        },
    },
    beforeUnmount() {
        if (this.photoPreviewUrl) {
            URL.revokeObjectURL(this.photoPreviewUrl);
        }
        if (this.rawImageUrl) {
            URL.revokeObjectURL(this.rawImageUrl);
        }
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
        max-width="max-w-4xl">
        <transition-container
            v-show="delayReady"
            :duration="800"
            type="slide-bottom">
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white/80 shadow-xl backdrop-blur-lg dark:border-slate-800 dark:bg-slate-900/80">
                <div
                    v-if="submitted"
                    class="p-8">
                    <div class="flex flex-col items-center rounded-xl border border-green-200 bg-green-50 p-6 text-center text-green-900">
                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-green-600">
                            <svg
                                class="h-8 w-8"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h2 class="text-2xl font-bold leading-tight">Check your email</h2>
                        <p class="mx-auto mt-3 max-w-md text-base text-green-800/80">
                            We sent a verification link to
                            <span class="font-bold">{{ submittedEmail }}</span>
                            . Check your inbox and confirm your email address so your registration can proceed to administrator review.
                        </p>
                        <Link
                            href="/"
                            class="mt-6 inline-flex rounded-lg bg-AB px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-AB/90">
                            Return to services
                        </Link>
                    </div>
                </div>

                <div
                    v-else-if="form"
                    class="flex flex-col">
                    <!-- Progress Header -->
                    <div class="border-b border-gray-100 bg-slate-50/50 p-6 dark:border-slate-800 dark:bg-slate-800/50">
                        <div class="mx-auto max-w-2xl">
                            <ProgressTabs
                                :steps="['Identity', 'Details', 'Role']"
                                :current="currentStep - 1"
                                :clickable="false" />
                        </div>
                    </div>

                    <form
                        @submit.prevent="submitRegistration"
                        class="relative min-h-[400px] overflow-hidden">
                        <!-- Step 1: Category -->
                        <transition
                            name="slide-fade"
                            mode="out-in">
                            <div
                                v-if="currentStep === 1"
                                key="step1"
                                class="flex min-h-[400px] flex-col items-center justify-center p-6 sm:p-10">
                                <h3 class="mb-8 text-xl font-bold text-gray-900 sm:text-2xl dark:text-gray-100">Select your personnel category</h3>

                                <div class="grid w-full max-w-3xl grid-cols-2 gap-4 sm:gap-6">
                                    <button
                                        type="button"
                                        @click="setCategory(true)"
                                        class="group flex flex-col items-center justify-center rounded-2xl border-2 p-4 text-center transition-all duration-300 sm:p-10"
                                        :class="form.is_philrice_employee === true ? 'border-AB bg-emerald-50/50 shadow-md dark:bg-emerald-900/20' : 'border-gray-200 bg-white/50 hover:border-AB/50 hover:bg-gray-50/50 hover:shadow-md dark:border-slate-700 dark:bg-slate-800/30 dark:hover:bg-slate-800/50'">
                                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 transition-all duration-300 group-hover:scale-110 group-hover:bg-emerald-200 sm:mb-6 sm:h-20 sm:w-20 dark:bg-emerald-900/40 dark:text-emerald-400 dark:group-hover:bg-emerald-800/60">
                                            <svg
                                                class="h-6 w-6 sm:h-10 sm:w-10"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                        <h4 class="mb-2 text-sm font-bold text-gray-900 sm:mb-3 sm:text-xl dark:text-gray-100">PhilRice Employee</h4>
                                        <p class="text-xs leading-relaxed text-gray-500 sm:text-sm dark:text-gray-400">Regular staff, contract of service, or direct hires of DA-PhilRice.</p>
                                    </button>

                                    <button
                                        type="button"
                                        @click="setCategory(false)"
                                        class="group flex flex-col items-center justify-center rounded-2xl border-2 p-4 text-center transition-all duration-300 sm:p-10"
                                        :class="form.is_philrice_employee === false ? 'border-blue-500 bg-blue-50/50 shadow-md dark:bg-blue-900/20' : 'border-gray-200 bg-white/50 hover:border-blue-400/50 hover:bg-gray-50/50 hover:shadow-md dark:border-slate-700 dark:bg-slate-800/30 dark:hover:bg-slate-800/50'">
                                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600 transition-all duration-300 group-hover:scale-110 group-hover:bg-blue-200 sm:mb-6 sm:h-20 sm:w-20 dark:bg-blue-900/40 dark:text-blue-400 dark:group-hover:bg-blue-800/60">
                                            <svg
                                                class="h-6 w-6 sm:h-10 sm:w-10"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 14l9-5-9-5-9 5 9 5zm0 0v6"></path>
                                            </svg>
                                        </div>
                                        <h4 class="mb-2 text-sm font-bold text-gray-900 sm:mb-3 sm:text-xl dark:text-gray-100">Student / OJT / Guest</h4>
                                        <p class="text-xs leading-relaxed text-gray-500 sm:text-sm dark:text-gray-400">On-the-job trainees, thesis students, and other external researchers.</p>
                                    </button>
                                </div>
                            </div>
                        </transition>

                        <!-- Step 2: Details -->
                        <transition
                            name="slide-fade"
                            mode="out-in">
                            <div
                                v-if="currentStep === 2"
                                key="step2"
                                class="flex min-h-[400px] flex-col p-6 sm:p-10">
                                <h3 class="mb-2 text-xl font-bold text-gray-900 sm:text-2xl dark:text-gray-100">Personal Details</h3>
                                <p class="mb-8 text-sm text-gray-500 dark:text-gray-400">Enter your legal name and contact information.</p>

                                <div class="mb-5 grid gap-5 md:grid-cols-4">
                                    <text-input
                                        required
                                        label="First Name"
                                        v-model="form.fname"
                                        :error="stepErrors.fname || form.errors.fname"
                                        autocomplete="given-name" />
                                    <text-input
                                        label="Middle Name"
                                        v-model="form.mname"
                                        :error="form.errors.mname"
                                        autocomplete="additional-name" />
                                    <text-input
                                        required
                                        label="Last Name"
                                        v-model="form.lname"
                                        :error="stepErrors.lname || form.errors.lname"
                                        autocomplete="family-name" />
                                    <text-input
                                        label="Suffix"
                                        v-model="form.suffix"
                                        :error="form.errors.suffix"
                                        autocomplete="honorific-suffix" />
                                </div>

                                <div class="mb-5 grid gap-5 md:grid-cols-2">
                                    <text-input
                                        required
                                        type="email"
                                        label="Active Email Address"
                                        v-model="form.email"
                                        :error="stepErrors.email || form.errors.email"
                                        autocomplete="email" />
                                    <text-input
                                        label="Phone Number"
                                        v-model="form.phone"
                                        :error="form.errors.phone"
                                        autocomplete="tel" />
                                </div>

                                <text-input
                                    label="Complete Address"
                                    v-model="form.address"
                                    :error="form.errors.address"
                                    autocomplete="street-address" />

                                <div class="mt-12 flex justify-between border-t border-gray-100 pt-6 dark:border-slate-800">
                                    <button
                                        type="button"
                                        @click="prevStep"
                                        class="rounded-xl border border-gray-300 px-6 py-2.5 font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-800">
                                        Back
                                    </button>
                                    <button
                                        type="button"
                                        @click="validateStep2"
                                        class="rounded-xl bg-AB px-8 py-2.5 font-bold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-AB/90">
                                        Next Step
                                    </button>
                                </div>
                            </div>
                        </transition>

                        <!-- Step 3: Role (PhilRice Employee) -->
                        <transition
                            name="slide-fade"
                            mode="out-in">
                            <div
                                v-if="currentStep === 3 && isPhilRiceEmployee"
                                key="step3_internal"
                                class="flex min-h-[400px] flex-col p-6 sm:p-10">
                                <h3 class="mb-2 text-xl font-bold text-gray-900 sm:text-2xl dark:text-gray-100">Employee Information</h3>
                                <p class="mb-8 text-sm text-gray-500 dark:text-gray-400">Provide your DA-PhilRice credentials.</p>

                                <div class="grid gap-6 rounded-2xl border border-gray-100 bg-gray-50/50 p-6 md:grid-cols-2 dark:border-slate-800 dark:bg-slate-800/50">
                                    <text-input
                                        required
                                        label="Position / Role"
                                        v-model="form.position"
                                        :error="form.errors.position"
                                        autocomplete="organization-title" />
                                    <text-input
                                        required
                                        label="PhilRice Employee ID"
                                        v-model="form.employee_id"
                                        :error="form.errors.employee_id" />
                                </div>

                                <div class="mt-12 flex justify-between border-t border-gray-100 pt-6 dark:border-slate-800">
                                    <button
                                        type="button"
                                        @click="prevStep"
                                        class="rounded-xl border border-gray-300 px-6 py-2.5 font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-800"
                                        :disabled="processing || model.api.processing">
                                        Back
                                    </button>
                                    <submit-btn
                                        class="rounded-xl bg-AB px-8 py-2.5 text-base font-bold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-AB/90"
                                        :disabled="processing || model.api.processing">
                                        <span v-if="processing || model.api.processing">Submitting...</span>
                                        <span v-else>Complete Registration</span>
                                    </submit-btn>
                                </div>
                            </div>
                        </transition>

                        <!-- Step 3: Role (External) -->
                        <transition
                            name="slide-fade"
                            mode="out-in">
                            <div
                                v-if="currentStep === 3 && !isPhilRiceEmployee"
                                key="step3_external"
                                class="flex min-h-[400px] flex-col p-6 sm:p-10">
                                <h3 class="mb-2 text-xl font-bold text-gray-900 sm:text-2xl dark:text-gray-100">Registration Requirements</h3>
                                <p class="mb-8 text-sm text-gray-500 dark:text-gray-400">Provide details to generate your temporary CBC ID card.</p>

                                <div class="grid gap-8 md:grid-cols-[1fr_14rem]">
                                    <div class="space-y-5">
                                        <div class="grid gap-5 md:grid-cols-2">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Personnel Type</label>
                                                <select
                                                    v-model="form.registration_type"
                                                    class="h-11 w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-AB focus:ring-AB dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                                                    <option
                                                        v-for="option in registrationTypeOptions"
                                                        :key="option.value"
                                                        :value="option.value">
                                                        {{ option.label }}
                                                    </option>
                                                </select>
                                            </div>
                                            <text-input
                                                required
                                                label="Year Level / Position"
                                                v-model="form.position"
                                                :error="form.errors.position" />
                                        </div>

                                        <text-input
                                            required
                                            label="Course / Program / Strand / Major"
                                            v-model="form.course_program"
                                            :error="form.errors.course_program" />

                                        <div class="grid gap-5 md:grid-cols-2">
                                            <text-input
                                                required
                                                label="Affiliation / School / Agency"
                                                v-model="form.affiliation"
                                                :error="form.errors.affiliation" />
                                            <div class="space-y-1.5">
                                                <text-input
                                                    required
                                                    type="date"
                                                    label="Access Expiry Date"
                                                    v-model="form.expires_at"
                                                    :error="form.errors.expires_at" />
                                                <p class="text-[11px] leading-tight text-gray-500">Access automatically expires after this date.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-center">
                                        <label class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                            2x2 ID Picture
                                            <b class="text-red-500">*</b>
                                        </label>
                                        <div class="group relative flex aspect-square w-full max-w-[14rem] items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50/50 shadow-inner transition-colors hover:border-AB/50 dark:border-slate-600 dark:bg-slate-800/50">
                                            <img
                                                v-if="photoPreviewUrl"
                                                :src="photoPreviewUrl"
                                                alt="Selected ID photo preview"
                                                class="h-full w-full object-cover" />
                                            <div
                                                v-else
                                                class="flex h-full w-full flex-col items-center justify-center p-6 text-center text-gray-400 transition-colors group-hover:text-AB dark:text-gray-500">
                                                <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm dark:bg-slate-700">
                                                    <svg
                                                        class="h-6 w-6"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                                <span class="text-sm font-medium">Click to upload</span>
                                            </div>

                                            <div class="absolute inset-0 flex cursor-pointer items-center justify-center bg-black/5 opacity-0 transition-opacity group-hover:opacity-100">
                                                <input
                                                    type="file"
                                                    accept="image/png,image/jpeg"
                                                    class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                                    @change="onPhotoSelected" />
                                            </div>
                                        </div>
                                        <InputError
                                            v-show="!!form.errors.id_photo"
                                            :message="form.errors.id_photo"
                                            class="mt-3 text-center" />
                                    </div>
                                </div>

                                <div class="mt-12 flex justify-between border-t border-gray-100 pt-6 dark:border-slate-800">
                                    <button
                                        type="button"
                                        @click="prevStep"
                                        class="rounded-xl border border-gray-300 px-6 py-2.5 font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-slate-600 dark:text-gray-300 dark:hover:bg-slate-800"
                                        :disabled="processing || model.api.processing">
                                        Back
                                    </button>
                                    <submit-btn
                                        class="rounded-xl bg-AB px-8 py-2.5 text-base font-bold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-AB/90"
                                        :disabled="processing || model.api.processing">
                                        <span v-if="processing || model.api.processing">Submitting...</span>
                                        <span v-else>Complete Registration</span>
                                    </submit-btn>
                                </div>
                            </div>
                        </transition>
                    </form>
                </div>
            </div>
        </transition-container>

        <ImageCropperModal
            :show="showCropModal"
            :image-url="rawImageUrl"
            @close="showCropModal = false"
            @cropped="onPhotoCropped" />

        <success-modal
            :show="showSubmittedModal"
            title="Registration Submitted"
            @close="showSubmittedModal = false">
            <template #content>
                <p>Your personnel registration has been submitted.</p>
                <p class="mt-2">
                    Check your Gmail inbox for the confirmation link sent to
                    <span class="font-semibold">{{ submittedEmail }}</span>
                    , then confirm your email address to continue.
                </p>
            </template>
            <template #footer>
                <div class="flex w-full justify-end gap-2">
                    <button
                        type="button"
                        class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50"
                        @click="showSubmittedModal = false">
                        Close
                    </button>
                    <Link
                        href="/"
                        class="inline-flex justify-center rounded-lg border border-transparent bg-AB px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-AB/90">
                        Return to services
                    </Link>
                </div>
            </template>
        </success-modal>
    </guest-form-page>
</template>

<style scoped>
.slide-fade-enter-active {
    transition: all 0.4s ease-out;
}
.slide-fade-leave-active {
    transition: all 0.3s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from {
    opacity: 0;
    transform: translateX(30px);
}
.slide-fade-leave-to {
    opacity: 0;
    transform: translateX(-30px);
}
</style>
