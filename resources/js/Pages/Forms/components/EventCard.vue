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
                backgroundText: this.resolveStyle("form-background-text-color", "text"),
                headerBox: this.resolveStyle("form-header-box", "background"),
                headerText: this.resolveStyle("form-header-box-text-color", "text"),
                timeFrom: this.resolveStyle("form-time-from", "background"),
                timeFromText: this.resolveStyle("form-time-from-text-color", "text"),
                timeTo: this.resolveStyle("form-time-to", "background"),
                timeToText: this.resolveStyle("form-time-to-text-color", "text"),
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
                        label: req.name || req.title || this.getFormTypeLabel(formType),
                        count,
                        isFull: maxSlots > 0 && count >= maxSlots,
                        maxSlots,
                    };
                });
        },
        visibleResponseTypes() {
            return this.requirementStats.filter((item) => item.count > 0 || item.maxSlots > 0);
        },
        totalResponseCount() {
            const breakdownTotal = this.visibleResponseTypes.reduce((acc, item) => acc + Number(item.count || 0), 0);
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
                    class: "bg-red-50 text-red-600 border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20 shadow-sm",
                    icon: "LuAlertCircle",
                };
            }
            if (this.formsData?.is_suspended) {
                return {
                    text: "Suspended",
                    class: "bg-amber-50 text-amber-600 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20 shadow-sm",
                    icon: "LuAlertTriangle",
                };
            }
            if (this.formsData?.is_active) {
                return {
                    text: "Active",
                    class: "bg-emerald-50 text-emerald-600 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20 shadow-sm",
                    icon: "LuCheckCircle2",
                };
            }
            return {
                text: "Draft",
                class: "bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-500/10 dark:text-slate-400 dark:border-slate-500/20 shadow-sm",
                icon: "LuCircle",
            };
        },
        dateRange() {
            const from = this.formsData?.date_from ? new Date(this.formsData.date_from) : null;
            const to = this.formsData?.date_to ? new Date(this.formsData.date_to) : null;
            if (!from || !to) return null;

            const sameMonth = from.getMonth() === to.getMonth() && from.getFullYear() === to.getFullYear();
            const fromStr = from.toLocaleDateString("en-US", {
                month: "short",
                day: "numeric",
            });
            const toStr = to.toLocaleDateString("en-US", {
                month: "short",
                day: "numeric",
                year: "numeric",
            });

            return sameMonth ? `${fromStr} - ${to.getDate()}, ${to.getFullYear()}` : `${fromStr} - ${toStr}`;
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
            if (this.formTypeLabels[normalized]) return this.formTypeLabels[normalized];
            return normalized.replace(/_/g, " ").replace(/\b\w/g, (char) => char.toUpperCase());
        },
        resolveStyle(tokenKey, type = "background") {
            const token = this.formsData?.style_tokens?.[tokenKey] ?? {};
            const value = token.value ?? null;
            if (!value || (typeof value === "string" && value.trim() === "")) return {};
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
        },
    },
};
</script>

<template>
    <div
        v-if="formsData"
        class="group relative flex w-full max-w-md flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white/80 shadow-sm backdrop-blur-xl transition-all duration-300 hover:shadow-xl dark:border-slate-700/60 dark:bg-slate-900/80"
        :class="{ 'opacity-70 grayscale-[0.3]': isExpired || formsData?.is_suspended }"
        :style="styles.background">
        <!-- Status Badge -->
        <div class="absolute right-4 top-4 z-20">
            <span
                class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-[0.65rem] font-bold uppercase tracking-widest backdrop-blur-md"
                :class="statusBadge.class">
                <component
                    :is="statusBadge.icon"
                    class="h-3.5 w-3.5" />
                {{ statusBadge.text }}
            </span>
        </div>

        <!-- Header Section -->
        <div
            class="relative border-b border-white/10 p-6 pb-5 dark:border-slate-800/50"
            :style="{ ...styles.headerBox, ...styles.headerText }">
            <div class="relative z-10 flex items-start justify-between gap-4">
                <div class="min-w-0 flex-1 pr-2">
                    <h3 class="text-normal mb-2 line-clamp-2 font-black leading-tight tracking-tight drop-shadow-md sm:text-lg">
                        {{ formsData.title }}
                    </h3>
                    <p class="line-clamp-2 text-sm font-medium leading-relaxed opacity-90 drop-shadow-sm">
                        {{ formsData.description }}
                    </p>
                </div>

                <!-- Event ID Frosted Badge -->
                <div class="flex shrink-0 flex-col items-center justify-center rounded-xl border border-white/20 bg-black/10 p-3 shadow-inner backdrop-blur-md dark:border-white/5 dark:bg-white/10">
                    <label class="text-2xl font-black leading-none tracking-tighter drop-shadow-md">
                        {{ formsData.event_id }}
                    </label>
                    <span class="mt-1.5 text-[0.6rem] font-bold uppercase tracking-widest opacity-80 drop-shadow-md">Event ID</span>
                </div>
            </div>
        </div>

        <!-- Date & Time Info -->
        <div class="border-b border-slate-100 bg-white/50 px-6 py-5 backdrop-blur-sm dark:border-slate-800 dark:bg-slate-900/50">
            <div class="flex flex-col gap-3">
                <div class="flex items-center gap-3 text-sm font-bold text-slate-700 dark:text-slate-200">
                    <div class="shrink-0 rounded-lg border border-indigo-100 bg-indigo-50 p-1.5 shadow-sm dark:border-indigo-500/20 dark:bg-indigo-500/10">
                        <LuCalendar class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                    </div>
                    <span class="truncate">
                        {{ dateRange || `${safeFormatDate(formsData.date_from)} - ${safeFormatDate(formsData.date_to)}` }}
                    </span>
                </div>

                <div class="flex items-center gap-3 text-sm font-bold text-slate-700 dark:text-slate-200">
                    <div class="shrink-0 rounded-lg border border-blue-100 bg-blue-50 p-1.5 shadow-sm dark:border-blue-500/20 dark:bg-blue-500/10">
                        <LuClock class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="flex items-center gap-2 truncate">
                        <span>{{ safeFormatTime(formsData.time_from) }}</span>
                        <LuArrowRight class="h-3.5 w-3.5 text-slate-400" />
                        <span>{{ safeFormatTime(formsData.time_to) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="flex-1 bg-slate-50/80 px-6 py-5 dark:bg-slate-800/40">
            <div class="mb-3.5 flex items-center justify-between">
                <span class="text-[0.65rem] font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">Responses</span>
                <span
                    v-if="visibleResponseTypes.length"
                    class="text-xs font-semibold text-slate-400 dark:text-slate-500">
                    {{ visibleResponseTypes.reduce((acc, item) => acc + item.count, 0) }} total
                </span>
            </div>

            <div
                v-if="visibleResponseTypes.length"
                class="grid grid-cols-2 gap-2.5 sm:grid-cols-3">
                <div
                    v-for="item in visibleResponseTypes"
                    :key="item.key"
                    class="relative rounded-xl border bg-white p-3 shadow-sm transition-all dark:bg-slate-800"
                    :class="item.isFull ? 'border-red-200 ring-1 ring-red-100 dark:border-red-900/50 dark:ring-red-900/30' : 'border-slate-200 dark:border-slate-700'">
                    <div class="mb-1.5 flex items-center justify-between">
                        <span
                            class="text-xl font-black tracking-tight"
                            :class="item.isFull ? 'text-red-600 dark:text-red-400' : 'text-slate-900 dark:text-slate-50'">
                            {{ item.count }}
                        </span>
                        <LuUsers
                            v-if="item.isFull"
                            class="h-4 w-4 text-red-500" />
                    </div>
                    <p class="line-clamp-2 text-[0.65rem] font-semibold leading-tight text-slate-500 dark:text-slate-400">
                        {{ item.label }}
                    </p>

                    <!-- Pulsing Indicator for Full Slots -->
                    <div
                        v-if="item.isFull"
                        class="absolute -right-1.5 -top-1.5">
                        <span class="relative flex h-3 w-3">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex h-3 w-3 rounded-full border-2 border-white bg-red-500 dark:border-slate-800"></span>
                        </span>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="py-6 text-center text-slate-400 dark:text-slate-500">
                <LuClipboardList class="mx-auto mb-2 h-8 w-8 opacity-40" />
                <p class="text-sm font-medium">
                    <template v-if="totalResponseCount > 0">
                        {{ totalResponseCount }}
                        {{ totalResponseCount === 1 ? "response" : "responses" }} recorded
                        <span
                            v-if="!visibleResponseTypes.length"
                            class="mt-0.5 block text-xs opacity-70">
                            (awaiting detailed breakdown)
                        </span>
                    </template>
                    <template v-else>No responses yet</template>
                </p>
            </div>
        </div>

        <!-- Quick Actions Bar -->
        <div class="border-t border-slate-100 bg-white px-5 py-3 dark:border-slate-800/80 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <!-- Group 1: Manage -->
                <div class="flex items-center gap-1">
                    <Link
                        :href="route('forms.update', formsData.event_id)"
                        class="rounded-xl p-2 text-slate-500 transition-colors hover:bg-blue-50 hover:text-blue-600 dark:text-slate-400 dark:hover:bg-blue-500/10 dark:hover:text-blue-400"
                        title="Edit form">
                        <LuSettings class="h-4 w-4" />
                    </Link>
                    <button
                        @click="copyLink"
                        class="rounded-xl p-2 text-slate-500 transition-colors hover:bg-indigo-50 hover:text-indigo-600 dark:text-slate-400 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-400"
                        title="Copy link">
                        <LuCopy class="h-4 w-4" />
                    </button>
                    <button
                        @click="downloadFormQr"
                        class="rounded-xl p-2 text-slate-500 transition-colors hover:bg-purple-50 hover:text-purple-600 dark:text-slate-400 dark:hover:bg-purple-500/10 dark:hover:text-purple-400"
                        title="Download QR">
                        <LuDownload class="h-4 w-4" />
                    </button>
                </div>

                <!-- Group 2: Actions -->
                <div class="flex items-center gap-1">
                    <Link
                        :href="route('forms.guest.index', formsData.event_id)"
                        target="_blank"
                        class="rounded-xl p-2 text-slate-500 transition-colors hover:bg-emerald-50 hover:text-emerald-600 dark:text-slate-400 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-400"
                        title="Preview Form">
                        <LuEye class="h-4 w-4" />
                    </Link>
                    <Link
                        :href="route('forms.scan', formsData.event_id)"
                        class="rounded-xl p-2 text-slate-500 transition-colors hover:bg-amber-50 hover:text-amber-600 dark:text-slate-400 dark:hover:bg-amber-500/10 dark:hover:text-amber-400"
                        title="Scan QR">
                        <LuScanLine class="h-4 w-4" />
                    </Link>
                    <suspend-form-btn
                        v-if="!isExpired"
                        :data="formsData"
                        @updated="updatedData = $event"
                        @failedUpdate="errors = $event"
                        class="p-2" />
                    <button
                        @click="confirmAction"
                        class="rounded-xl p-2 text-slate-500 transition-colors hover:bg-red-50 hover:text-red-600 dark:text-slate-400 dark:hover:bg-red-500/10 dark:hover:text-red-400"
                        title="Delete Form">
                        <LuTrash2 class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <p
                v-if="errors?.message"
                class="mt-2.5 text-center text-[0.7rem] font-bold text-red-600 dark:text-red-400">
                <LuAlertCircle class="-mt-0.5 mr-1 inline h-3.5 w-3.5" />
                {{ errors.message }}
            </p>
        </div>

        <!-- Hidden QR Download -->
        <div
            ref="formQrDownloadHost"
            class="hidden"
            aria-hidden="true">
            <qrcode-vue
                v-if="formGuestUrl"
                :value="formGuestUrl"
                :size="500"
                level="M"
                render-as="canvas"
                @ready="qrDownloadReady = true" />
        </div>

        <!-- Delete Confirmation -->
        <delete-confirmation-modal
            :show="confirmDelete"
            :is-processing="model.api.processing"
            title="Delete Event Form"
            message="This action cannot be undone. All responses and data will be permanently removed."
            :item-name="formsData.title"
            @confirm="handleDelete"
            @close="confirmDelete = false" />
    </div>
</template>
