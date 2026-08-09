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
            this.form.registration_type = isPhilRice ? 'philrice_employee' : 'student';
            this.nextStep();
        },
        validateStep2() {
            this.stepErrors = {};
            let valid = true;
            if (!this.form.fname) { this.stepErrors.fname = 'First name is required.'; valid = false; }
            if (!this.form.lname) { this.stepErrors.lname = 'Last name is required.'; valid = false; }
            if (!this.form.email) { this.stepErrors.email = 'Email is required.'; valid = false; }
            
            if (this.form.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.form.email)) {
                this.stepErrors.email = 'Valid email is required.'; valid = false;
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
            
            event.target.value = '';
        },
        onPhotoCropped(blob) {
            const file = new File([blob], 'id_photo.jpg', { type: blob.type });
            this.form.id_photo = file;
            
            if (this.photoPreviewUrl) {
                URL.revokeObjectURL(this.photoPreviewUrl);
            }
            
            this.photoPreviewUrl = URL.createObjectURL(blob);
        },
        syncRegistrationType() {
            if (this.isPhilRiceEmployee) {
                this.form.registration_type = 'philrice_employee';
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
        max-width="max-w-4xl"
    >
        <transition-container v-show="delayReady" :duration="800" type="slide-bottom">
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div v-if="submitted" class="p-8">
                    <div class="rounded-xl border border-green-200 bg-green-50 p-6 text-green-900 text-center flex flex-col items-center">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4 text-green-600">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h2 class="font-bold text-2xl leading-tight">Check your email</h2>
                        <p class="mt-3 text-base text-green-800/80 max-w-md mx-auto">
                            We sent a verification link to <span class="font-bold">{{ submittedEmail }}</span>. Check your inbox and confirm your email address so your registration can proceed to administrator review.
                        </p>
                        <Link href="/" class="inline-flex mt-6 rounded-lg bg-AB px-6 py-3 text-sm font-semibold text-white hover:bg-AB/90 transition shadow-sm">
                            Return to services
                        </Link>
                    </div>
                </div>

                <div v-else-if="form" class="flex flex-col">
                    <!-- Progress Header -->
                    <div class="bg-slate-50 border-b border-gray-100 p-6">
                        <div class="max-w-2xl mx-auto">
                            <ProgressTabs 
                                :steps="['Identity', 'Details', 'Role']" 
                                :current="currentStep - 1" 
                                :clickable="false" 
                            />
                        </div>
                    </div>

                    <form @submit.prevent="submitRegistration" class="relative overflow-hidden min-h-[400px]">
                        <!-- Step 1: Category -->
                        <transition name="slide-fade" mode="out-in">
                            <div v-if="currentStep === 1" key="step1" class="p-6 sm:p-10 flex flex-col items-center justify-center min-h-[400px]">
                                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-8">Select your personnel category</h3>
                                
                                <div class="grid sm:grid-cols-2 gap-6 w-full max-w-3xl">
                                    <button type="button" @click="setCategory(true)"
                                            class="flex flex-col items-center justify-center p-8 sm:p-10 text-center rounded-2xl border-2 transition-all duration-300 group"
                                            :class="form.is_philrice_employee === true ? 'border-AB bg-emerald-50/50 shadow-md' : 'border-gray-200 hover:border-AB/50 hover:bg-gray-50 hover:shadow-md bg-white'">
                                        <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-emerald-200 transition-all duration-300">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        </div>
                                        <h4 class="text-xl font-bold text-gray-900 mb-3">PhilRice Employee</h4>
                                        <p class="text-sm text-gray-500 leading-relaxed">Regular staff, contract of service, or direct hires of DA-PhilRice.</p>
                                    </button>

                                    <button type="button" @click="setCategory(false)"
                                            class="flex flex-col items-center justify-center p-8 sm:p-10 text-center rounded-2xl border-2 transition-all duration-300 group"
                                            :class="form.is_philrice_employee === false ? 'border-blue-500 bg-blue-50/50 shadow-md' : 'border-gray-200 hover:border-blue-400/50 hover:bg-gray-50 hover:shadow-md bg-white'">
                                        <div class="w-20 h-20 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center mb-6 group-hover:scale-110 group-hover:bg-blue-200 transition-all duration-300">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6"></path></svg>
                                        </div>
                                        <h4 class="text-xl font-bold text-gray-900 mb-3">Student / OJT / Guest</h4>
                                        <p class="text-sm text-gray-500 leading-relaxed">On-the-job trainees, thesis students, and other external researchers.</p>
                                    </button>
                                </div>
                            </div>
                        </transition>

                        <!-- Step 2: Details -->
                        <transition name="slide-fade" mode="out-in">
                            <div v-if="currentStep === 2" key="step2" class="p-6 sm:p-10 flex flex-col min-h-[400px]">
                                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Personal Details</h3>
                                <p class="text-sm text-gray-500 mb-8">Enter your legal name and contact information.</p>

                                <div class="grid md:grid-cols-4 gap-5 mb-5">
                                    <text-input required label="First Name" v-model="form.fname" :error="stepErrors.fname || form.errors.fname" autocomplete="given-name" />
                                    <text-input label="Middle Name" v-model="form.mname" :error="form.errors.mname" autocomplete="additional-name" />
                                    <text-input required label="Last Name" v-model="form.lname" :error="stepErrors.lname || form.errors.lname" autocomplete="family-name" />
                                    <text-input label="Suffix" v-model="form.suffix" :error="form.errors.suffix" autocomplete="honorific-suffix" />
                                </div>

                                <div class="grid md:grid-cols-2 gap-5 mb-5">
                                    <text-input required type="email" label="Active Email Address" v-model="form.email" :error="stepErrors.email || form.errors.email" autocomplete="email" />
                                    <text-input label="Phone Number" v-model="form.phone" :error="form.errors.phone" autocomplete="tel" />
                                </div>
                                
                                <text-input label="Complete Address" v-model="form.address" :error="form.errors.address" autocomplete="street-address" />

                                <div class="mt-12 flex justify-between border-t border-gray-100 pt-6">
                                    <button type="button" @click="prevStep" class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition shadow-sm">
                                        Back
                                    </button>
                                    <button type="button" @click="validateStep2" class="px-8 py-2.5 rounded-xl bg-AB text-white font-bold shadow-md hover:bg-AB/90 transition hover:-translate-y-0.5">
                                        Next Step
                                    </button>
                                </div>
                            </div>
                        </transition>

                        <!-- Step 3: Role (PhilRice Employee) -->
                        <transition name="slide-fade" mode="out-in">
                            <div v-if="currentStep === 3 && isPhilRiceEmployee" key="step3_internal" class="p-6 sm:p-10 flex flex-col min-h-[400px]">
                                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Employee Information</h3>
                                <p class="text-sm text-gray-500 mb-8">Provide your DA-PhilRice credentials.</p>

                                <div class="grid md:grid-cols-2 gap-6 bg-gray-50 p-6 rounded-2xl border border-gray-100">
                                    <text-input required label="Position / Role" v-model="form.position" :error="form.errors.position" autocomplete="organization-title" />
                                    <text-input required label="PhilRice Employee ID" v-model="form.employee_id" :error="form.errors.employee_id" />
                                </div>

                                <div class="mt-12 flex justify-between border-t border-gray-100 pt-6">
                                    <button type="button" @click="prevStep" class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition shadow-sm" :disabled="processing || model.api.processing">
                                        Back
                                    </button>
                                    <submit-btn class="px-8 py-2.5 rounded-xl bg-AB text-white font-bold shadow-md hover:bg-AB/90 transition text-base hover:-translate-y-0.5" :disabled="processing || model.api.processing">
                                        <span v-if="processing || model.api.processing">Submitting...</span>
                                        <span v-else>Complete Registration</span>
                                    </submit-btn>
                                </div>
                            </div>
                        </transition>

                        <!-- Step 3: Role (External) -->
                        <transition name="slide-fade" mode="out-in">
                            <div v-if="currentStep === 3 && !isPhilRiceEmployee" key="step3_external" class="p-6 sm:p-10 flex flex-col min-h-[400px]">
                                <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Registration Requirements</h3>
                                <p class="text-sm text-gray-500 mb-8">Provide details to generate your temporary CBC ID card.</p>

                                <div class="grid md:grid-cols-[1fr_14rem] gap-8">
                                    <div class="space-y-5">
                                        <div class="grid md:grid-cols-2 gap-5">
                                            <div class="space-y-1.5">
                                                <label class="text-sm font-semibold text-gray-700">Personnel Type</label>
                                                <select v-model="form.registration_type" class="w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-AB focus:ring-AB h-11">
                                                    <option v-for="option in registrationTypeOptions" :key="option.value" :value="option.value">
                                                        {{ option.label }}
                                                    </option>
                                                </select>
                                            </div>
                                            <text-input required label="Year Level / Position" v-model="form.position" :error="form.errors.position" />
                                        </div>

                                        <text-input required label="Course / Program / Strand / Major" v-model="form.course_program" :error="form.errors.course_program" />
                                        
                                        <div class="grid md:grid-cols-2 gap-5">
                                            <text-input required label="Affiliation / School / Agency" v-model="form.affiliation" :error="form.errors.affiliation" />
                                            <div class="space-y-1.5">
                                                <text-input required type="date" label="Access Expiry Date" v-model="form.expires_at" :error="form.errors.expires_at" />
                                                <p class="text-[11px] text-gray-500 leading-tight">Access automatically expires after this date.</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col items-center">
                                        <label class="text-sm font-semibold text-gray-700 mb-3">2x2 ID Picture <b class="text-red-500">*</b></label>
                                        <div class="aspect-square w-full max-w-[14rem] overflow-hidden rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 flex items-center justify-center relative group shadow-inner transition-colors hover:border-AB/50">
                                            <img v-if="photoPreviewUrl" :src="photoPreviewUrl" alt="Selected ID photo preview" class="h-full w-full object-cover" />
                                            <div v-else class="flex flex-col h-full w-full items-center justify-center p-6 text-center text-gray-400 group-hover:text-AB transition-colors">
                                                <div class="w-12 h-12 rounded-full bg-white shadow-sm flex items-center justify-center mb-3">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                </div>
                                                <span class="text-sm font-medium">Click to upload</span>
                                            </div>
                                            
                                            <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer bg-black/5">
                                                <input type="file" accept="image/png,image/jpeg" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" @change="onPhotoSelected" />
                                            </div>
                                        </div>
                                        <InputError v-show="!!form.errors.id_photo" :message="form.errors.id_photo" class="mt-3 text-center" />
                                    </div>
                                </div>

                                <div class="mt-12 flex justify-between border-t border-gray-100 pt-6">
                                    <button type="button" @click="prevStep" class="px-6 py-2.5 rounded-xl border border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 transition shadow-sm" :disabled="processing || model.api.processing">
                                        Back
                                    </button>
                                    <submit-btn class="px-8 py-2.5 rounded-xl bg-AB text-white font-bold shadow-md hover:bg-AB/90 transition text-base hover:-translate-y-0.5" :disabled="processing || model.api.processing">
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
            @cropped="onPhotoCropped"
        />

        <success-modal
            :show="showSubmittedModal"
            title="Registration Submitted"
            @close="showSubmittedModal = false"
        >
            <template #content>
                <p>Your personnel registration has been submitted.</p>
                <p class="mt-2">
                    Check your Gmail inbox for the confirmation link sent to <span class="font-semibold">{{ submittedEmail }}</span>, then confirm your email address to continue.
                </p>
            </template>
            <template #footer>
                <div class="flex w-full justify-end gap-2">
                    <button type="button" class="inline-flex justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50" @click="showSubmittedModal = false">
                        Close
                    </button>
                    <Link href="/" class="inline-flex justify-center rounded-lg border border-transparent bg-AB px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-AB/90">
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
