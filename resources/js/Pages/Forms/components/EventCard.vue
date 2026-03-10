<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Form from "@/Modules/domain/Form.js";
import SuspendFormBtn from "@/Pages/Forms/components/SuspendFormBtn.vue";
import DtoResponse from "@/Modules/dto/DtoResponse";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import Participant from "@/Modules/domain/Participant";
import RequirementsManager from "@/Components/Forms/RequirementsManager.vue";
import QrcodeVue from "qrcode.vue";

export default {
    name: "EventCard",

    components: {
        QrcodeVue,
        RequirementsManager,
        SuspendFormBtn,
    },

    mixins: [ApiMixin, DataFormatterMixin],

    data() {
        return {
            confirmDelete: false,
            updatedData: null,
            errors: null,
            qrDownloadReady: false,
        };
    },

    computed: {
        Form() {
            return Form;
        },
        formsData() {
            if (this.updatedData instanceof DtoResponse) {
                return this.updatedData.data;
            }
            return this.data ?? null;
        },
        formTypeLabels() {
            return {
                pre_registration: "Pre-registration",
                pre_registration_biotech: "Pre-registration + Quiz Bee",
                pre_registration_quizbee: "Pre-registration Quiz Bee",
                preregistration: "Pre-registration",
                preregistration_biotech: "Pre-registration + Quiz Bee",
                preregistration_quizbee: "Pre-registration Quiz Bee",
                registration: "Registration",
                pre_test: "Pre-test",
                post_test: "Post-test",
                feedback: "Feedback",
            };
        },
        styles() {
            return {
                background: this.resolveStyle('form-background', 'background'),
                backgroundText: this.resolveStyle('form-background-text-color', 'text'),
                headerBox: this.resolveStyle('form-header-box', 'background'),
                headerText: this.resolveStyle('form-header-box-text-color', 'text'),
                timeFrom: this.resolveStyle('form-time-from', 'background'),
                timeFromText: this.resolveStyle('form-time-from-text-color', 'text'),
                timeTo: this.resolveStyle('form-time-to', 'background'),
                timeToText: this.resolveStyle('form-time-to-text-color', 'text'),
            };
        },
        requirementStats() {
            if (!Array.isArray(this.formsData?.requirements)) {
                return [];
            }

            return this.formsData.requirements
                .filter(req => !!req)
                .map((req, index) => {
                    const formType = req.form_type || `custom_${index}`;
                    const count = req.responses_count ?? 0;
                    const maxSlots = Number(req.max_slots ?? 0);

                    return {
                        key: req.id || formType,
                        form_type: formType,
                        label: req.name || req.title || this.getFormTypeLabel(formType),
                        count,
                        isFull: maxSlots > 0 && count >= maxSlots,
                    };
                });
        },
        responsesByType() {
            if (this.requirementStats.length) {
                return this.requirementStats.map(item => ({
                    form_type: item.form_type,
                    label: item.label,
                    count: item.count,
                }));
            }

            if (!this.formsData?.responses_by_type) {
                return [];
            }

            return Object.entries(this.formsData.responses_by_type).map(
                ([key, count]) => ({
                    form_type: key,
                    label: this.getFormTypeLabel(key),
                    count: count ?? 0,
                })
            );
        },
        visibleResponseTypes() {
            return this.requirementStats.filter(item => item.count > 0);
        },
        hasRightBorder() {
            return (index) => index < this.visibleResponseTypes.length - 1;
        },
        formGuestUrl() {
            if (!this.formsData?.event_id) {
                return "";
            }

            return route('forms.guest.index', this.formsData.event_id);
        },
    },
    beforeMount() {
        this.model = new Form();
        this.startCountdown();
    },
    methods: {
        safeFormatDate(value) {
            return value ? this.formatDate(value) : "-";
        },
        safeFormatTime(value) {
            return value ? this.formatTime(value) : "-";
        },
        confirmAction() {
            this.confirmDelete = true;
        },
        async handleDelete() {
            this.toDelete = { event_id: this.formsData?.event_id };
            const response = await this.submitDelete();

            if (response instanceof DtoResponse) {
                this.confirmDelete = false;
                this.$emit("deletedModel", response.data);
            }
        },
        async handleExport(eventId, filename) {
            if (!eventId) return;

            this.model = new Participant();
            this.setFormAction("get");

            this.form.filter = "event_id";
            this.form.search = eventId;
            this.form.is_exact = true;

            const response = await this.fetchData();
            await this.exportCSV(response.data, filename);

            this.model = new Form();
        },
        async downloadFormQr() {
            if (!this.formsData?.event_id) {
                return;
            }

            if (!this.qrDownloadReady) {
                await this.$nextTick();
            }

            const qrHost = this.$refs.formQrDownloadHost;
            const canvas = qrHost?.querySelector?.('canvas');

            if (!canvas) {
                return;
            }

            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/png');
            link.download = `event-${this.formsData.event_id}-qr-500x500.png`;
            document.body.appendChild(link);
            link.click();
            link.remove();
        },
        getFormTypeLabel(formType) {
            if (!formType) {
                return "Form";
            }

            const normalized = String(formType).trim();
            if (this.formTypeLabels[normalized]) {
                return this.formTypeLabels[normalized];
            }

            return normalized
                .replace(/_/g, " ")
                .replace(/\b\w/g, char => char.toUpperCase());
        },

        resolveStyle(tokenKey, type = 'background') {
            const token = this.formsData?.style_tokens?.[tokenKey] ?? {};
            const value = token.value ?? null;

            if (!value || (typeof value === 'string' && value.trim() === '')) return {};

            const mode = token.mode ?? null;

            // Image backgrounds
            if (mode === 'image') {
                if (type === 'background') {
                    return {
                        backgroundImage: `url(${value})`,
                        backgroundSize: 'cover',
                        backgroundRepeat: 'no-repeat',
                        backgroundPosition: 'center',
                    };
                }
                return {};
            }

            // Colors or fallback values
            if (type === 'background') {
                return { backgroundColor: value };
            }

            if (type === 'text') {
                return { color: value };
            }

            return {};
        }
    },
};
</script>


<template>
    <div v-if="formsData" class="flex flex-col gap-2 justify-between mx-auto bg-gray-100 border rounded-md min-w-xl max-w-xl" :style="{ ...styles.background, ...styles.backgroundText }">
        <div class="p-2 flex flex-col gap-2 lg:max-w-2xl w-full ">
            <div id="form-header-box" class="flex flex-row p-2 rounded-md justify-between shadow py-4 gap-2 bg-AB text-gray-50" :style="{ ...styles.headerBox, ...styles.headerText }">
                <div class="flex flex-col min-h-[3rem] gap-1">
                    <label class="leading-none font-bold">{{ formsData.title }}</label>
                    <p class="leading-snug line-clamp-2 text-sm">
                        {{ formsData.description }}
                    </p>
                </div>
                <div class="flex flex-col items-start md:items-center justify-center">
                    <label class="text-xl md:text-4xl leading-none font-[1000]">{{ formsData.event_id }}</label>
                    <span class="text-[0.65rem] leading-none ">Event ID</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 px-1 text-sm" :style="styles.backgroundText">
                <div>
                    <span class="font-semibold uppercase">Start Date: </span>
                    <label>{{ formatDate(formsData.date_from) }}</label>
                </div>
                <div>
                    <span class="font-semibold uppercase">End Date: </span>
                    <label>{{ formatDate(formsData.date_to) }}</label>
                </div>
                <div>
                    <span class="font-semibold uppercase">Start Time: </span>
                    <label>{{ formatTime(formsData.time_from) }}</label>
                </div>
                <div>
                    <span class="font-semibold uppercase">End Time: </span>
                    <label>{{ formatTime(formsData.time_to) }}</label>
                </div>
            </div>
            <div class="px-1 min-h-[4rem] hidden">
                <div class="line-clamp-1">
                    <span class="font-bold uppercase">Venue: </span>
                    <label class="leading-snug break-all">{{ formsData.venue }}</label>
                </div>
                <p class="text-sm leading-none line-clamp-3 break-all">{{ formsData.details }}</p>
            </div>
            <div class="flex flex-col text-white gap-1">
                <div v-if="isExpired" class="px-1 flex w-full">
                    <div v-show="isExpired" class="relative w-full min-w-full">
                        <div class="flex flex-col p-2 bg-gray-600 w-full text-red-600" >
                            <span class="font-bold uppercase leading-none text-center">This Form is expired</span>
                            <span class="leading-none text-xs text-center">adjust date or time to reopen</span>
                        </div>
                    </div>
                </div>
                <div v-else class="flex w-full">
                    <transition-container type="slide-right" :duration="1000">
                        <div v-show="formsData.is_suspended" v-if="formsData.is_suspended" class="relative w-full min-w-full bg-yellow-300 rounded-md">
                            <div class="flex flex-col p-2 text-red-600" >
                                <span class="font-bold uppercase leading-none text-center">This Form is suspended</span>
                                <span class="leading-none text-xs text-center">unable to accept request</span>
                            </div>
                        </div>
                        <span v-else-if="visibleResponseTypes.length" class="font-bold uppercase text-center pt-2 text-sm mx-auto text-gray-800">Statistics</span>
                        <span v-else class="font-bold uppercase text-center text-sm p-2 mx-auto text-gray-800">No Responses</span>
                    </transition-container>
                </div>
                <div class="flex gap-1 justify-center flex-wrap w-full overflow-x-auto bg-AA rounded-md">
                    <template v-for="item in visibleResponseTypes" :key="item.key">
                        <div
                            :class="[
                                'flex flex-col items-center px-2 py-1 flex-shrink-0',
                                item.isFull ? 'text-red-600' : ''
                            ]"
                        >
                            <label class="text-lg md:text-xl font-[1000]">{{ item.count }}</label>
                            <span class="text-[0.55rem] md:text-[0.6rem] text-center line-clamp-2">{{ item.label }}</span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        <div class="flex flex-col p-2">
                <div class="flex gap-1 justify-center flex-wrap">
                    <Link :href="route('forms.update', formsData.event_id)" class="bg-blue-200 text-blue-900 w-fit px-2 py-1 rounded flex items-center hover:scale-110 duration-200" title="Modify details in the form">
                        <setting-icon class="w-4 h-4" />
                    </Link>
                    
                    <a :href="route('forms.guest.index', formsData.event_id)" target="_blank" class="bg-green-200 text-green-900 w-fit px-2 py-1 rounded flex items-center hover:scale-110 duration-200" title="Preview form">
                        <view-icon class="w-4 h-4" />
                    </a>

                    <button @click="downloadFormQr" class="bg-indigo-200 text-indigo-900 w-fit px-2 py-1 rounded flex items-center hover:scale-110 duration-200" title="Download 500x500 QR code for this guest form">
                        <download-qr-icon class="w-4 h-4" />
                    </button>

                    <Link :href="route('forms.scan', formsData.event_id)" class="bg-AA text-white w-fit px-2 py-1 rounded flex items-center hover:scale-110 duration-200" title="Scan QR code for on-site registration and attendance">
                        <scan-icon class="w-4 h-4" />
                    </Link>

                    <suspend-form-btn v-if="!isExpired" :data="formsData" @updated="updatedData = $event" @failedUpdate="errors = $event"/>

                    <button @click="confirmAction"  class="bg-red-200 text-red-900 w-fit px-2 py-1 rounded flex items-center hover:scale-110 duration-200" title="Permanently remove this form">
                        <DeleteIcon class="w-4 h-4" />
                    </button>
                </div>
                <label class="text-xs text-center text-red-600">{{errors?.toObject()?.message}}</label>
            </div>
            <div ref="formQrDownloadHost" class="hidden" aria-hidden="true">
                <qrcode-vue
                    v-if="formGuestUrl"
                    :value="formGuestUrl"
                    :size="500"
                    level="M"
                    render-as="canvas"
                    @ready="qrDownloadReady = true"
                />
            </div>
            <delete-confirmation-modal
                :show="confirmDelete"
                :is-processing="model.api.processing"
                title="Confirm Delete Form"
                :message="`This will permanently delete the form. This action cannot be undone.`"
                :item-name="formsData.title"
                @confirm="handleDelete"
                @close="confirmDelete = false"
            />
    </div>
</template>

<style scoped>

</style>
