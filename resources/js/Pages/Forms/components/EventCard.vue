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
            showActions: false,
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
                background: this.resolveStyle("form-background", "background"),
                backgroundText: this.resolveStyle(
                    "form-background-text-color",
                    "text",
                ),
                headerBox: this.resolveStyle("form-header-box", "background"),
                headerText: this.resolveStyle(
                    "form-header-box-text-color",
                    "text",
                ),
                timeFrom: this.resolveStyle("form-time-from", "background"),
                timeFromText: this.resolveStyle(
                    "form-time-from-text-color",
                    "text",
                ),
                timeTo: this.resolveStyle("form-time-to", "background"),
                timeToText: this.resolveStyle(
                    "form-time-to-text-color",
                    "text",
                ),
            };
        },
        requirementStats() {
            if (!Array.isArray(this.formsData?.requirements)) {
                return [];
            }
            return this.formsData.requirements
                .filter((req) => !!req)
                .map((req, index) => {
                    const formType = req.form_type || `custom_${index}`;
                    const count = req.responses_count ?? 0;
                    const maxSlots = Number(req.max_slots ?? 0);
                    return {
                        key: req.id || formType,
                        form_type: formType,
                        label:
                            req.name ||
                            req.title ||
                            this.getFormTypeLabel(formType),
                        count,
                        isFull: maxSlots > 0 && count >= maxSlots,
                        maxSlots,
                    };
                });
        },
        visibleResponseTypes() {
            return this.requirementStats.filter(
                (item) => item.count > 0 || item.maxSlots > 0,
            );
        },
        totalResponseCount() {
            const breakdownTotal = this.visibleResponseTypes.reduce(
                (acc, item) => acc + Number(item.count || 0),
                0,
            );
            if (breakdownTotal > 0) {
                return breakdownTotal;
            }
            return Number(this.formsData?.responses_count ?? 0);
        },
        formGuestUrl() {
            if (!this.formsData?.event_id) {
                return "";
            }
            return route("forms.guest.index", this.formsData.event_id);
        },
        statusBadge() {
            if (this.isExpired) {
                return {
                    text: "Expired",
                    class: "bg-red-100 text-red-700 border-red-200",
                    icon: "LuAlertCircle",
                };
            }
            if (this.formsData?.is_suspended) {
                return {
                    text: "Suspended",
                    class: "bg-amber-100 text-amber-700 border-amber-200",
                    icon: "LuAlertTriangle",
                };
            }
            if (this.formsData?.is_active) {
                return {
                    text: "Active",
                    class: "bg-green-100 text-green-700 border-green-200",
                    icon: "LuCheckCircle",
                };
            }
            return {
                text: "Draft",
                class: "bg-gray-100 text-gray-700 border-gray-200",
                icon: "LuCircle",
            };
        },
        dateRange() {
            const from = this.formsData?.date_from
                ? new Date(this.formsData.date_from)
                : null;
            const to = this.formsData?.date_to
                ? new Date(this.formsData.date_to)
                : null;
            if (!from || !to) return null;

            const sameMonth =
                from.getMonth() === to.getMonth() &&
                from.getFullYear() === to.getFullYear();
            const fromStr = from.toLocaleDateString("en-US", {
                month: "short",
                day: "numeric",
            });
            const toStr = to.toLocaleDateString("en-US", {
                month: "short",
                day: "numeric",
                year: "numeric",
            });

            return sameMonth
                ? `${fromStr} - ${to.getDate()}, ${to.getFullYear()}`
                : `${fromStr} - ${toStr}`;
        },
    },
    beforeMount() {
        this.model = new Form();
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
            if (!this.formsData?.event_id) return;
            if (!this.qrDownloadReady) await this.$nextTick();

            const qrHost = this.$refs.formQrDownloadHost;
            const canvas = qrHost?.querySelector?.("canvas");
            if (!canvas) return;

            const link = document.createElement("a");
            link.href = canvas.toDataURL("image/png");
            link.download = `event-${this.formsData.event_id}-qr.png`;
            document.body.appendChild(link);
            link.click();
            link.remove();
        },
        getFormTypeLabel(formType) {
            if (!formType) return "Form";
            const normalized = String(formType).trim();
            if (this.formTypeLabels[normalized])
                return this.formTypeLabels[normalized];
            return normalized
                .replace(/_/g, " ")
                .replace(/\b\w/g, (char) => char.toUpperCase());
        },
        resolveStyle(tokenKey, type = "background") {
            const token = this.formsData?.style_tokens?.[tokenKey] ?? {};
            const value = token.value ?? null;
            if (!value || (typeof value === "string" && value.trim() === ""))
                return {};
            const mode = token.mode ?? null;

            if (mode === "image") {
                if (type === "background") {
                    return {
                        backgroundImage: `url(${value})`,
                        backgroundSize: "cover",
                        backgroundRepeat: "no-repeat",
                        backgroundPosition: "center",
                    };
                }
                return {};
            }

            if (type === "background") return { backgroundColor: value };
            if (type === "text") return { color: value };
            return {};
        },
        copyLink() {
            navigator.clipboard.writeText(this.formGuestUrl);
            // Could add toast notification here
        },
    },
};
</script>

<template>
    <div
        v-if="formsData"
        class="group relative bg-white dark:bg-gray-800 rounded-2xl shadow-sm hover:shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-300 max-w-md w-full"
        :class="{ 'opacity-75': isExpired || formsData?.is_suspended }"
        :style="styles.background"
    >
        <!-- Status Badge -->
        <div class="absolute top-3 right-3 z-10">
            <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-medium border"
                :class="statusBadge.class"
            >
                <component :is="statusBadge.icon" class="w-3.5 h-3.5" />
                {{ statusBadge.text }}
            </span>
        </div>

        <!-- Header Section -->
        <div
            class="relative p-5 pb-4 bg-AB text-gray-50"
            :style="{ ...styles.headerBox, ...styles.headerText }"
        >
            <div class="relative flex justify-between items-center gap-3">
                <div class="flex-1 min-w-0">
                    <h3
                        class="text-lg font-bold text-gray-50 leading-tight line-clamp-2 mb-1"
                    >
                        {{ formsData.title }}
                    </h3>
                    <p
                        class="text-sm text-gray-50/80 line-clamp-2 leading-relaxed"
                    >
                        {{ formsData.description }}
                    </p>
                </div>

                <div class="flex flex-col items-center justify-center">
                    <label class="text-xl md:text-4xl leading-none font-[1000]">
                        {{ formsData.event_id }}
                    </label>
                    <span class="text-[0.65rem] leading-none">Event ID</span>
                </div>
            </div>
        </div>

        <!-- Date & Time Info -->
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <div
                class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 mb-3"
            >
                <LuCalendar class="w-4 h-4 text-gray-400" />
                <span class="font-medium">{{
                    dateRange ||
                    `${safeFormatDate(formsData.date_from)} - ${safeFormatDate(formsData.date_to)}`
                }}</span>
            </div>

            <div class="flex items-center gap-4 text-sm">
                <div
                    class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300"
                >
                    <LuClock class="w-4 h-4 text-gray-400" />
                    <span>{{ safeFormatTime(formsData.time_from) }}</span>
                </div>
                <LuArrowRight class="w-4 h-4 text-gray-300" />
                <div
                    class="flex items-center gap-1.5 text-gray-600 dark:text-gray-300"
                >
                    <LuClock class="w-4 h-4 text-gray-400" />
                    <span>{{ safeFormatTime(formsData.time_to) }}</span>
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="px-5 py-4 bg-gray-50/50 dark:bg-gray-700/30">
            <div class="flex items-center justify-between mb-3">
                <span
                    class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"
                    >Responses</span
                >
                <span
                    v-if="visibleResponseTypes.length"
                    class="text-xs text-gray-400 dark:text-gray-500"
                >
                    {{
                        visibleResponseTypes.reduce(
                            (acc, item) => acc + item.count,
                            0,
                        )
                    }}
                    total
                </span>
            </div>

            <div
                v-if="visibleResponseTypes.length"
                class="grid grid-cols-2 sm:grid-cols-3 gap-2"
            >
                <div
                    v-for="item in visibleResponseTypes"
                    :key="item.key"
                    class="relative p-3 rounded-xl bg-white dark:bg-gray-700 border border-gray-100 dark:border-gray-600 shadow-sm"
                    :class="{
                        'ring-2 ring-red-100 dark:ring-red-900/30': item.isFull,
                    }"
                >
                    <div class="flex items-center justify-between mb-1">
                        <span
                            class="text-2xl font-bold"
                            :class="
                                item.isFull
                                    ? 'text-red-600 dark:text-red-400'
                                    : 'text-gray-900 dark:text-gray-50'
                            "
                        >
                            {{ item.count }}
                        </span>
                        <LuUsers
                            v-if="item.isFull"
                            class="w-4 h-4 text-red-500"
                        />
                    </div>
                    <p
                        class="text-[0.65rem] text-gray-500 dark:text-gray-400 leading-tight line-clamp-2"
                    >
                        {{ item.label }}
                    </p>
                    <div v-if="item.isFull" class="absolute -top-1 -right-1">
                        <span class="flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"
                            ></span>
                            <span
                                class="relative inline-flex rounded-full h-2 w-2 bg-red-500"
                            ></span>
                        </span>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="text-center py-6 text-gray-400 dark:text-gray-500"
            >
                <LuClipboardList class="w-8 h-8 mx-auto mb-2 opacity-30" />
                <p class="text-sm">
                    <template v-if="totalResponseCount > 0">
                        {{ totalResponseCount }} {{ totalResponseCount === 1 ? 'response' : 'responses' }} recorded
                        <span v-if="!visibleResponseTypes.length">(awaiting detailed breakdown)</span>
                    </template>
                    <template v-else>
                        No responses yet
                    </template>
                </p>
            </div>
        </div>

        <!-- Quick Actions Bar -->
        <div
            class="px-5 py-3 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700"
        >
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1">
                    <Link
                        :href="route('forms.update', formsData.event_id)"
                        class="p-2 rounded-lg text-gray-600 hover:text-blue-600 hover:bg-blue-50 dark:text-gray-400 dark:hover:text-blue-400 dark:hover:bg-blue-900/20 transition-colors"
                        title="Edit form"
                    >
                        <LuSettings class="w-4 h-4" />
                    </Link>

                    <button
                        @click="copyLink"
                        class="p-2 rounded-lg text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 dark:text-gray-400 dark:hover:text-indigo-400 dark:hover:bg-indigo-900/20 transition-colors"
                        title="Copy link"
                    >
                        <LuCopy class="w-4 h-4" />
                    </button>

                    <button
                        @click="downloadFormQr"
                        class="p-2 rounded-lg text-gray-600 hover:text-purple-600 hover:bg-purple-50 dark:text-gray-400 dark:hover:text-purple-400 dark:hover:bg-purple-900/20 transition-colors"
                        title="Download QR"
                    >
                        <LuDownload class="w-4 h-4" />
                    </button>
                </div>

                <div class="flex items-center gap-1">
                    <Link
                        :href="route('forms.guest.index', formsData.event_id)"
                        target="_blank"
                        class="p-2 rounded-lg text-gray-600 hover:text-green-600 hover:bg-green-50 dark:text-gray-400 dark:hover:text-green-400 dark:hover:bg-green-900/20 transition-colors"
                        title="Preview"
                    >
                        <LuEye class="w-4 h-4" />
                    </Link>

                    <Link
                        :href="route('forms.scan', formsData.event_id)"
                        class="p-2 rounded-lg text-gray-600 hover:text-amber-600 hover:bg-amber-50 dark:text-gray-400 dark:hover:text-amber-400 dark:hover:bg-amber-900/20 transition-colors"
                        title="Scan QR"
                    >
                        <LuScanLine class="w-4 h-4" />
                    </Link>

                    <suspend-form-btn
                        v-if="!isExpired"
                        :data="formsData"
                        @updated="updatedData = $event"
                        @failedUpdate="errors = $event"
                        class="p-2"
                    />

                    <button
                        @click="confirmAction"
                        class="p-2 rounded-lg text-gray-600 hover:text-red-600 hover:bg-red-50 dark:text-gray-400 dark:hover:text-red-400 dark:hover:bg-red-900/20 transition-colors"
                        title="Delete"
                    >
                        <LuTrash2 class="w-4 h-4" />
                    </button>
                </div>
            </div>

            <p
                v-if="errors?.message"
                class="mt-2 text-xs text-red-600 dark:text-red-400 text-center"
            >
                <LuAlertCircle class="w-3 h-3 inline mr-1" />
                {{ errors.message }}
            </p>
        </div>

        <!-- Hidden QR Download -->
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

        <!-- Delete Confirmation -->
        <delete-confirmation-modal
            :show="confirmDelete"
            :is-processing="model.api.processing"
            title="Delete Event Form"
            message="This action cannot be undone. All responses and data will be permanently removed."
            :item-name="formsData.title"
            @confirm="handleDelete"
            @close="confirmDelete = false"
        />
    </div>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
