<script>
import { useForm } from "@inertiajs/vue3";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import LaboratoryPersonnelMixin from "@/Modules/mixins/LaboratoryPersonnelMixin";
import PersonnelLookup from "@/Components/PersonnelLookup.vue";
import SelectSearchField from "@/Components/SelectSearchField.vue";
import TextInput from "@/Components/TextInput.vue";
import SuccessModal from "@/Components/SuccessModal.vue";
import FlagIcon from '@/Components/Icons/FlagIcon.vue';

export default {
    name: "LaboratoryEquipmentShow",
    components: {
        PersonnelLookup,
        SelectSearchField,
        TextInput,
        SuccessModal,
        FlagIcon,
    },
    mixins: [ApiMixin, DataFormatterMixin, LaboratoryPersonnelMixin],
    props: {
        equipment_id: {
            type: String,
            default: null,
        },
    },
    data() {
        return {
            delayReady: false,
            title: "Laboratory Equipment Log",
            subtitle: "Scan-based tracking for equipment usage",
            selectedEquipmentId: this.equipment_id,
            equipmentOptions: [],
            equipment: null,
            activeLog: null,
            allowedActions: [],
            maxEndUseHours: 24,
            loading: false,
            message: null,
            messageType: "success",
            showSuccessModal: false,
            personnelPreview: null,
            checkInErrors: {},
            checkOutErrors: {},
            checkInForm: useForm({
                employee_id: "",
                end_use_at: "",
                purpose: "",
            }),
            checkOutForm: useForm({
                employee_id: "",
                admin_override: false,
            }),
        };
    },
    computed: {
        equipmentId() {
            return this.selectedEquipmentId || this.equipment_id || null;
        },
        hasEquipment() {
            return !!this.equipmentId;
        },
        canCheckIn() {
            return this.allowedActions.includes("check-in");
        },
        canCheckOut() {
            return this.allowedActions.includes("check-out");
        },
    },
    methods: {
        async loadEquipmentOptions() {
            try {
                const response = await this.fetchGetApi("api.laboratory.equipments.index");
                const payload = response?.data ?? response;
                const list = Array.isArray(payload) ? payload : payload?.data ?? [];

                this.equipmentOptions = list.map((item) => {
                    const name = item.name || "Equipment";
                    const brand = item.brand ? ` (${item.brand})` : "";
                    return {
                        id: item.equipment_id || item.id,
                        name: `${name}${brand}`,
                    };
                });
            } catch (error) {
                this.equipmentOptions = [];
            }
        },
        async loadEquipment() {
            if (!this.equipmentId) {
                return;
            }
            this.loading = true;
            try {
                const response = await this.fetchGetApi("api.laboratory.equipments.show", {
                    routeParams: this.equipmentId,
                });
                const details = response?.data ?? response;

                this.equipment = details?.equipment ?? null;
                this.activeLog = details?.active_log ?? null;
                this.allowedActions = details?.allowed_actions ?? [];
                this.maxEndUseHours = details?.max_end_use_hours ?? 24;
            } catch (error) {
                this.messageType = "error";
                this.message = error?.response?.data?.message || "Unable to load equipment details.";
            } finally {
                this.loading = false;
            }
        },
        handlePersonnelFound(data) {
            this.personnelPreview = data;
            this.checkInErrors = { ...this.checkInErrors, employee_id: null };
            // Save to localStorage for future check-outs
            this.saveLaboratoryPersonnel({
                employee_id: this.checkInForm.employee_id,
                fullName: data.fullName,
                fname: data.fname,
                mname: data.mname,
                lname: data.lname,
                suffix: data.suffix,
            });
        },
        handlePersonnelError(error) {
            this.checkInErrors = { ...this.checkInErrors, [error.field]: error.message };
        },
        searchDifferentPersonnel() {
            this.clearLaboratoryPersonnel();
            this.checkOutForm.reset();
            this.checkOutErrors = {};
        },
        resetCheckIn() {
            this.checkInForm.reset();
            this.checkInErrors = {};
            this.personnelPreview = null;
        },
        resetCheckOut() {
            this.checkOutForm.reset();
            this.checkOutErrors = {};
        },
        async submitCheckIn() {
            this.checkInErrors = {};
            this.message = null;

            const payload = {
                employee_id: this.checkInForm.employee_id,
                end_use_at: this.checkInForm.end_use_at,
                purpose: this.checkInForm.purpose,
            };

            try {
                await this.fetchPostApi("api.laboratory.equipments.check-in", payload, {
                    routeParams: this.equipmentId,
                });
                this.messageType = "success";
                this.message = "Check-in recorded successfully.";
                this.showSuccessModal = true;
                this.resetCheckIn();
                await this.loadEquipment();
            } catch (error) {
                this.messageType = "error";
                if (error?.response?.status === 422) {
                    this.checkInErrors = error.response.data.errors || { base: error.response.data.message };
                } else {
                    this.checkInErrors = { base: error?.response?.data?.message || "Check-in failed." };
                }
            }
        },
        async submitCheckOut() {
            this.checkOutErrors = {};
            this.message = null;

            const payload = {
                employee_id: this.checkOutForm.employee_id,
                admin_override: this.checkOutForm.admin_override,
            };

            try {
                await this.fetchPostApi("api.laboratory.equipments.check-out", payload, {
                    routeParams: this.equipmentId,
                });
                this.messageType = "success";
                this.message = "Check-out recorded successfully.";
                this.showSuccessModal = true;
                this.resetCheckOut();
                await this.loadEquipment();
            } catch (error) {
                this.messageType = "error";
                if (error?.response?.status === 422) {
                    this.checkOutErrors = error.response.data.errors || { base: error.response.data.message };
                } else {
                    this.checkOutErrors = { base: error?.response?.data?.message || "Check-out failed." };
                }
            }
        },
        formatPersonnelName(personnel) {
            if (!personnel) return "-";
            const parts = [personnel.fname, personnel.mname, personnel.lname, personnel.suffix]
                .filter(Boolean)
                .map((value) => String(value).trim())
                .filter(Boolean);
            return parts.length ? parts.join(" ") : "-";
        },
        getErrorMessage(error) {
            if (!error) return null;
            return typeof error === 'string' ? error : Array.isArray(error) ? error[0] : error;
        },
    },
    watch: {
        equipmentId(newValue, oldValue) {
            if (newValue === oldValue) return;
            if (!newValue) {
                this.equipment = null;
                this.activeLog = null;
                this.allowedActions = [];
                return;
            }
            this.loadEquipment();
        },
    },
    mounted() {
        if (this.equipmentId) {
            this.loadEquipment();
        } else {
            this.loadEquipmentOptions();
        }
        this.loadLaboratoryPersonnel();
        // Auto-fill check-out form with saved personnel
        if (this.savedLaboratoryPersonnel?.employee_id) {
            this.checkOutForm.employee_id = this.savedLaboratoryPersonnel.employee_id;
        }
        setTimeout(() => {
            this.delayReady = true;
        }, 200);
    },
};
</script>

<template>
    <Head :title="title" />
    <SuccessModal
        :show="showSuccessModal"
        :title="message"
        @close="showSuccessModal = false"
    />
    <GuestFormPage :title="title" :subtitle="subtitle" :delay-ready="delayReady">
        <!-- Loading Overlay -->
        <transition name="fade">
            <div v-if="processing" class="fixed top-0 left-0 w-full h-full z-[60] flex items-center justify-center bg-black bg-opacity-30">
                <div class="flex flex-col items-center gap-3 bg-white rounded-lg p-6 shadow-lg">
                    <div class="animate-spin rounded-full h-10 w-10 border-4 border-gray-300 border-t-AB"></div>
                    <p class="text-sm text-gray-600 font-medium">Processing request...</p>
                </div>
            </div>
        </transition>

        <transition-container v-show="delayReady" :duration="1000" type="slide-bottom">
            <div class="flex flex-col gap-4 w-full max-w-4xl mx-auto p-2 bg-gray-100 md:rounded-md h-full md:h-fit">
                <div class="flex flex-col gap-2 border rounded-lg bg-white p-4 shadow-sm">

                    <div v-if="!hasEquipment" class="mt-3">
                        <SelectSearchField
                            id="equipment_selector"
                            label="Select equipment"
                            placeholder="Search by name, ID, brand, or barcode"
                            :options="equipmentOptions"
                            v-model="selectedEquipmentId"
                        />
                        <p class="text-xs text-gray-500 mt-2">
                            Scan the QR code if available, or search and select equipment from the list.
                        </p>
                    </div>
                    <div v-else-if="loading" class="text-sm text-gray-500">Loading equipment details...</div>
                    <div v-else-if="equipment" class="grid grid-cols-1 md:grid-cols-2 gap-1">
                        <div class="col-span-2 flex justify-between pb-1 leading-none">
                            <h1 class="text-xl font-bold">{{ equipment.name }}</h1>
                            <button v-if="!equipment_id" @click="selectedEquipmentId = null">
                                <close-icon class="w-6 h-6 text-red-600" />
                            </button>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs uppercase text-gray-500">Brand</span>
                            <span class="font-semibold">{{ equipment.brand || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs uppercase text-gray-500">Description</span>
                            <span class="font-semibold">{{ equipment.description || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs uppercase text-gray-500">PhilRice Property No.</span>
                            <span class="font-semibold">{{ equipment.barcode_prri || '-' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs uppercase text-gray-500">CBC Barcode</span>
                            <span class="font-semibold">{{ equipment.barcode || '-' }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="hasEquipment" class="grid grid-cols-1 gap-4">
                    <div class="border rounded-lg bg-white p-4 shadow-sm">
                        <h2 class="text-base font-semibold mb-2">Current Status</h2>
                        <div v-if="activeLog" class="flex flex-col gap-1 text-sm">
                            <div class="flex justify-between gap-1">
                                <span class="text-gray-500">Status</span>
                                <span class="font-semibold uppercase">{{ activeLog.status }}</span>
                            </div>
                            <div class="flex justify-between gap-1">
                                <span class="text-gray-500">Checked in at</span>
                                <span>{{ formatDateTime(activeLog.started_at) }}</span>
                            </div>
                            <div class="flex justify-between gap-1">
                                <span class="text-gray-500">Expected end</span>
                                <span>{{ formatDateTime(activeLog.end_use_at) }}</span>
                            </div>
                            <div class="flex justify-between gap-1">
                                <span class="text-gray-500">User</span>
                                <span>{{ formatPersonnelName(activeLog.personnel) }}</span>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-500">
                            No active user for this equipment
                        </div>
                    </div>
                </div>

                <div v-if="hasEquipment && canCheckIn" class="border rounded-lg bg-white p-4 shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 mb-3 gap-2">
                        <h2 class="text-base font-semibold">Check-in Equipment</h2>
                        <p class="text-sm text-gray-500">Lookup your PhilRice ID to auto-fill details.</p>
                    </div>

                    <div class="flex flex-col gap-1">
                        <PersonnelLookup
                            v-model="checkInForm.employee_id"
                            @found="handlePersonnelFound"
                            @error="handlePersonnelError"
                        />
                        <div v-if="personnelPreview" class="text-center w-full text-xs text-AC">Hi! {{ personnelPreview.fullName }}</div>
                        <div v-if="getErrorMessage(checkInErrors.employee_id)" class="text-center w-full text-xs text-red-600">
                            {{ getErrorMessage(checkInErrors.employee_id) }}
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-2">
                        <TextInput
                            id="end_use_at"
                            v-model="checkInForm.end_use_at"
                            label="Estimated End of Use"
                            type="datetime-local"
                            :error="getErrorMessage(checkInErrors.end_use_at)"
                            @keydown.enter.prevent="submitCheckIn"
                            required
                        />
                        <TextInput
                            id="purpose"
                            v-model="checkInForm.purpose"
                            label="Purpose (optional)"
                            :error="getErrorMessage(checkInErrors.purpose)"
                            @keydown.enter.prevent="submitCheckIn"
                        />
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        End of use must be within {{ maxEndUseHours }} hours from now.
                    </p>

                    <div v-if="checkInErrors.base" class="text-sm text-red-600 mt-2">{{ checkInErrors.base }}</div>

                    <button
                        type="button"
                        class="mt-3 px-4 py-2 rounded bg-AB text-white text-sm hover:bg-AB-dark w-full"
                        @click="submitCheckIn"
                    >
                        Check In Equipment
                    </button>
                </div>

                <div v-if="hasEquipment && canCheckOut" class="border rounded-lg bg-white p-4 shadow-sm flex flex-col justify-end gap-2">
                    <div class="flex justify-between gap-5 items-center px-2">
                        <h2 class="text-base font-semibold w-fit">Check-out Equipment</h2>
                        <a
                            :href="route('suppEquipReports.create.guest', equipment.barcode)"
                            target="_blank"
                            title="Report an issue with this equipment"
                            rel="noopener noreferrer"
                            class="flex flex-row items-center gap-1 text-red-600 p-1 px-2 w-fit text-xs rounded-full"
                        >
                            <flag-icon class="h-5 w-3" />
                            Report
                        </a>
                    </div>

                    <!-- Show saved personnel info as label -->
                    <div v-if="savedLaboratoryPersonnel" class="flex gap-2 justify-between items-center px-2 pt-3">
                        <div class="text-sm text-gray-600">As: <span class="font-semibold">{{ savedLaboratoryPersonnel.fullName }} ( {{ savedLaboratoryPersonnel.employee_id }} )</span></div>
                        <button
                            v-if="savedLaboratoryPersonnel"
                            type="button"
                            title="Clear"
                            class="p-1 rounded h-fit"
                            @click="searchDifferentPersonnel"
                        >
                            <close-icon class="w-5 h-5 text-red-600" />
                        </button>
                    </div>

                    <div v-if="!savedLaboratoryPersonnel"  class="flex gap-2 items-end px-2">
                        <div class="flex-1">
                            <TextInput
                                id="checkout_employee_id"
                                v-model="checkOutForm.employee_id"
                                :label="savedLaboratoryPersonnel ? 'Use different PhilRice ID' : 'PhilRice ID'"
                                :error="getErrorMessage(checkOutErrors.employee_id)"
                                @keydown.enter.prevent="submitCheckOut"
                            />
                        </div>
                    </div>

                    <div v-if="getErrorMessage(checkOutErrors.base)" class="text-sm text-red-600 mt-2">{{ getErrorMessage(checkOutErrors.base) }}</div>

                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-2 w-fit justify-end px-2">
                            <input id="admin_override" v-model="checkOutForm.admin_override" type="checkbox" class="rounded-full" />
                            <label for="admin_override" class="text-xs leading-none">Admin Override</label>
                        </div>
                        <button
                            type="button"
                            class="px-4 py-2 rounded bg-AB text-white text-sm hover:bg-AA w-full"
                            @click="submitCheckOut"
                        >
                            Check Out Equipment
                        </button>
                    </div>
                </div>
            </div>
        </transition-container>
    </GuestFormPage>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.fade-enter-to,
.fade-leave-from {
    opacity: 1;
}
</style>
