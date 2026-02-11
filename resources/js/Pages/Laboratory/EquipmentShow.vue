<script>
import { useForm } from "@inertiajs/vue3";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import PersonnelLookup from "@/Components/PersonnelLookup.vue";
import SelectSearchField from "@/Components/SelectSearchField.vue";
import TextInput from "@/Components/TextInput.vue";

export default {
    name: "LaboratoryEquipmentShow",
    components: {
        PersonnelLookup,
        SelectSearchField,
        TextInput,
    },
    mixins: [ApiMixin, DataFormatterMixin],
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
        },
        handlePersonnelError(error) {
            this.checkInErrors = { ...this.checkInErrors, [error.field]: error.message };
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
        setTimeout(() => {
            this.delayReady = true;
        }, 200);
    },
};
</script>

<template>
    <Head :title="title" />
    <GuestFormPage :title="title" :subtitle="subtitle" :delay-ready="delayReady">
        <transition-container v-show="delayReady" :duration="1000" type="slide-bottom">
            <div class="flex flex-col gap-4 w-full max-w-4xl mx-auto p-2 bg-gray-100 md:rounded-md">
                <div class="flex flex-col gap-2 border rounded-lg bg-white p-4 shadow-sm">
                    <div v-if="message" :class="messageType === 'error' ? 'text-red-600' : 'text-green-600'" class="text-sm">
                        {{ message }}
                    </div>

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
                        <div class="col-span-2 pb-1">
                            <h1 class="text-xl font-bold">{{ equipment.name }}</h1>
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
                        <div v-if="activeLog" class="flex flex-col gap-2 text-sm">
                            <div class="flex justify-between gap-5">
                                <span class="text-gray-500">Status</span>
                                <span class="font-semibold uppercase">{{ activeLog.status }}</span>
                            </div>
                            <div class="flex justify-between gap-5">
                                <span class="text-gray-500">Checked in at</span>
                                <span>{{ formatDateTime(activeLog.started_at) }}</span>
                            </div>
                            <div class="flex justify-between gap-5">
                                <span class="text-gray-500">Expected end</span>
                                <span>{{ formatDateTime(activeLog.end_use_at) }}</span>
                            </div>
                            <div class="flex justify-between gap-5">
                                <span class="text-gray-500">User</span>
                                <span>{{ formatPersonnelName(activeLog.personnel) }}</span>
                            </div>
                        </div>
                        <div v-else class="text-sm text-gray-500">
                            No active log for this equipment.
                        </div>
                    </div>
                </div>

                <div v-if="hasEquipment && canCheckIn" class="border rounded-lg bg-white p-4 shadow-sm">
                    <div class="grid grid-cols-1 md:grid-cols-2 mb-3 gap-2">
                        <h2 class="text-base font-semibold">Check-in Equipment</h2>
                        <p class="text-sm text-gray-500">Lookup your PhilRice ID to auto-fill details.</p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <PersonnelLookup
                            v-model="checkInForm.employee_id"
                            @found="handlePersonnelFound"
                            @error="handlePersonnelError"
                        />
                        <div v-if="checkInErrors.employee_id" class="text-sm text-red-600">
                            {{ checkInErrors.employee_id }}
                        </div>
                        <div v-if="personnelPreview" class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-gray-500">Name</span>
                                <div class="font-semibold">{{ personnelPreview.fullName }}</div>
                            </div>
                            <div>
                                <span class="text-gray-500">Position</span>
                                <div class="font-semibold">{{ personnelPreview.position || '-' }}</div>
                            </div>
                            <div>
                                <span class="text-gray-500">Affiliation</span>
                                <div class="font-semibold">{{ personnelPreview.affiliation || '-' }}</div>
                            </div>
                            <div>
                                <span class="text-gray-500">Contact</span>
                                <div class="font-semibold">{{ personnelPreview.phone || '-' }}</div>
                            </div>
                            <div>
                                <span class="text-gray-500">Email</span>
                                <div class="font-semibold">{{ personnelPreview.email || '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-2">
                        <TextInput
                            id="end_use_at"
                            v-model="checkInForm.end_use_at"
                            label="Estimated End of Use"
                            type="datetime-local"
                            :error="checkInErrors.end_use_at"
                            @keydown.enter.prevent="submitCheckIn"
                            required
                        />
                        <TextInput
                            id="purpose"
                            v-model="checkInForm.purpose"
                            label="Purpose (optional)"
                            :error="checkInErrors.purpose"
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

                <div v-if="hasEquipment && canCheckOut" class="border rounded-lg bg-white p-4 shadow-sm">
                    <div class="grid grid-cols-2 mb-3 gap-5">
                        <h2 class="text-base font-semibold w-fit">Check-out Equipment</h2>
                        <div class="flex items-center gap-2 w-fit justify-end">
                            <input id="admin_override" v-model="checkOutForm.admin_override" type="checkbox" class="rounded" />
                            <label for="admin_override" class="text-sm">Admin Override</label>
                        </div>
                    </div>
                    <TextInput
                        id="checkout_employee_id"
                        v-model="checkOutForm.employee_id"
                        label="PhilRice ID"
                        :error="checkOutErrors.employee_id"
                        @keydown.enter.prevent="submitCheckOut"
                        required
                    />

                    <div v-if="checkOutErrors.base" class="text-sm text-red-600 mt-2">{{ checkOutErrors.base }}</div>

                    <button
                        type="button"
                        class="mt-3 px-4 py-2 rounded bg-AB text-white text-sm hover:bg-AA w-full"
                        @click="submitCheckOut"
                    >
                        Check Out Equipment
                    </button>
                </div>
            </div>
        </transition-container>
    </GuestFormPage>
</template>
