<script>
import { Link, router } from "@inertiajs/vue3";
import QrcodeVue from "qrcode.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DtoResponse from "@/Modules/dto/DtoResponse";
import GoLink from "@/Modules/domain/GoLink";
import { 
    Link2, 
    QrCode, 
    Sparkles, 
    Save, 
    X, 
    RotateCcw, 
    Trash2, 
    Loader2, 
    Download, 
    ExternalLink 
} from 'lucide-vue-next';

export default {
    name: "GoLinkForm",
    components: { 
        Link, 
        QrcodeVue,
        Link2, 
        QrCode, 
        Sparkles, 
        Save, 
        X, 
        RotateCcw, 
        Trash2, 
        Loader2, 
        Download, 
        ExternalLink 
    },
    mixins: [ApiMixin],
    props: {
        data: {
            type: Object,
            default: null,
        },
    },
    data() {
        return {
            rememberFormKey: "golinks",
            qrCanvasId: `golink-qr-${Math.random().toString(36).slice(2, 10)}`,
        };
    },
    computed: {
        isEdit() {
            return !!this.data;
        },
        publicBaseUrl() {
            return (this.$page.props.publicBaseUrl || "").replace(/\/$/, "");
        },
        publicUrlPreview() {
            const slug = this.form?.slug?.trim();
            return slug ? `${this.publicBaseUrl}/go/${slug}` : "";
        },
        formTitle() {
            return this.isEdit ? "Update Branded Redirect" : "Create Branded Redirect";
        },
        formDescription() {
            return this.isEdit
                ? "Edit the redirect metadata, expiration, and destination stored in the external WordPress table."
                : "Manage the record from onecbc while generating public links on the dacbc domain.";
        },
        submitLabel() {
            if (this.processing) {
                return this.isEdit ? "Saving Changes..." : "Creating Go Link...";
            }

            return this.isEdit ? "Save Changes" : "Create Go Link";
        },
    },
    beforeMount() {
        this.model = new GoLink(this.data ?? {});
        this.currentFormAction = this.isEdit ? "update" : "create";
        this.form = this.createFormWithRemember(
            this.isEdit
                ? this.model.updateFields(this.data)
                : this.model.createFields(),
            this.currentFormAction
        );
        this.form.expires = this.normalizeDateTimeLocal(this.form.expires);

        if (!this.isEdit && !this.form.slug) {
            this.generateSlug();
        }
    },
    methods: {
        generateSlug(length = 9) {
            const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
            let result = "";

            for (let index = 0; index < length; index += 1) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }

            this.form.slug = result;
        },
        normalizeDateTimeLocal(value) {
            if (!value) {
                return null;
            }

            return String(value).slice(0, 16);
        },
        resetToSource() {
            if (!this.isEdit) {
                this.resetField(this.model.createFields());
                this.generateSlug();
                return;
            }

            this.resetField(this.model.updateFields(this.data));
            this.form.expires = this.normalizeDateTimeLocal(this.form.expires);
        },
        downloadQrCode() {
            if (!this.publicUrlPreview) {
                return;
            }

            const wrapper = this.$refs.qrWrapper;
            const canvas = wrapper instanceof HTMLElement
                ? wrapper.querySelector("canvas")
                : null;

            if (!(canvas instanceof HTMLCanvasElement)) {
                return;
            }

            const safeSlug = (this.form.slug || "go-link").replace(/[^A-Za-z0-9_-]/g, "-");
            const link = document.createElement("a");
            link.href = canvas.toDataURL("image/png");
            link.download = `${safeSlug}-qr.png`;
            link.click();
        },
        async submitProxy() {
            const response = this.isEdit
                ? await this.submitUpdate()
                : await this.submitCreate();

            if (response instanceof DtoResponse) {
                router.visit(route("golinks.index"));
            }
        },
        async handleDelete() {
            if (!window.confirm("Delete this Go Link? This action cannot be undone.")) {
                return;
            }

            this.toDelete = { id: this.data?.id };
            const response = await this.submitDelete();

            if (response instanceof DtoResponse) {
                router.visit(route("golinks.index"));
            }
        },
    },
};
</script>

<template>
    <form v-if="form" @submit.prevent="submitProxy" class="mx-auto max-w-5xl space-y-6 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-2xl shadow-sm border border-slate-200/60 dark:border-slate-800 p-6 md:p-8">
        
        <!-- Header -->
        <div class="flex items-start gap-4 border-b border-slate-100 dark:border-slate-800/60 pb-6">
            <div class="p-3 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20 rounded-xl shadow-sm shrink-0">
                <Link2 class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
            </div>
            <div>
                <p class="text-[0.65rem] font-bold uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-1">Go Link Manager</p>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">{{ formTitle }}</h2>
                <p class="mt-1.5 max-w-2xl text-sm font-medium leading-relaxed text-slate-500 dark:text-slate-400">
                    {{ formDescription }}
                </p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            
            <!-- Left Column: Inputs -->
            <div class="space-y-5">
                <text-input
                    required
                    label="Target URL"
                    v-model="form.target_url"
                    :error="form.errors.target_url"
                    guide="Destination must be a full URL."
                    placeholder="https://example.com/destination"
                />

                <!-- Slug Field with Generator -->
                <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-4 shadow-sm space-y-2">
                    <label class="block text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">Slug</label>
                    <div class="flex gap-2">
                        <input
                            v-model="form.slug"
                            type="text"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2 text-sm font-medium text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 shadow-sm transition-colors"
                            placeholder="Leave empty to auto-generate"
                        >
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-2 text-xs font-bold uppercase tracking-widest text-slate-700 dark:text-slate-300 transition-all hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm shrink-0 active:scale-95"
                            @click="generateSlug"
                        >
                            <Sparkles class="w-3.5 h-3.5 text-emerald-500" /> Generate
                        </button>
                    </div>
                    <p v-if="form.errors.slug" class="text-xs font-semibold text-rose-600 dark:text-rose-400">{{ form.errors.slug }}</p>
                    <p class="text-[0.65rem] font-medium text-slate-400 dark:text-slate-500 truncate">
                        Preview: <span class="font-mono text-slate-600 dark:text-slate-300">{{ publicUrlPreview || `${publicBaseUrl}/go/[auto-generated]` }}</span>
                    </p>
                </div>

                <text-input
                    label="Expires"
                    type="datetime-local"
                    v-model="form.expires"
                    :error="form.errors.expires"
                    guide="Optional expiration timestamp for the redirect."
                />

                <!-- Checkboxes -->
                <div class="grid gap-3 sm:grid-cols-2">
                    <label class="group relative flex items-start gap-3 p-4 rounded-xl border-2 transition-all duration-300 cursor-pointer shadow-sm"
                        :class="form.status ? 'bg-emerald-50/50 border-emerald-400 dark:bg-emerald-500/10 dark:border-emerald-500/50' : 'bg-white border-slate-200/60 hover:border-emerald-300 dark:bg-slate-900/50 dark:border-slate-700/60 dark:hover:border-emerald-500/50'">
                        <input
                            v-model="form.status"
                            type="checkbox"
                            class="mt-0.5 rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                        />
                        <div>
                            <span class="block text-sm font-bold text-slate-900 dark:text-white">Active</span>
                            <span class="mt-0.5 block text-xs font-medium leading-relaxed text-slate-500 dark:text-slate-400">
                                Inactive links stop redirecting.
                            </span>
                        </div>
                    </label>

                    <label class="group relative flex items-start gap-3 p-4 rounded-xl border-2 transition-all duration-300 cursor-pointer shadow-sm"
                        :class="form.is_public ? 'bg-indigo-50/50 border-indigo-400 dark:bg-indigo-500/10 dark:border-indigo-500/50' : 'bg-white border-slate-200/60 hover:border-indigo-300 dark:bg-slate-900/50 dark:border-slate-700/60 dark:hover:border-indigo-500/50'">
                        <input
                            v-model="form.is_public"
                            type="checkbox"
                            class="mt-0.5 rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                        />
                        <div>
                            <span class="block text-sm font-bold text-slate-900 dark:text-white">Public Flow</span>
                            <span class="mt-0.5 block text-xs font-medium leading-relaxed text-slate-500 dark:text-slate-400">
                                Originate from public submission.
                            </span>
                        </div>
                    </label>
                </div>

                <!-- OG Metadata -->
                <div class="grid gap-4 md:grid-cols-2">
                    <text-input label="OG Title" v-model="form.og_title" :error="form.errors.og_title" />
                    <text-input label="OG Image URL" v-model="form.og_image" :error="form.errors.og_image" />
                </div>

                <text-area label="OG Description" v-model="form.og_description" :error="form.errors.og_description" />
            </div>

            <!-- Right Column: Previews & QR Code -->
            <div class="space-y-5">
                
                <!-- Public Link Preview Card -->
                <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm space-y-2">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                        <ExternalLink class="w-3.5 h-3.5" /> Public Link Preview
                    </p>
                    <a
                        v-if="publicUrlPreview"
                        :href="publicUrlPreview"
                        target="_blank"
                        rel="noopener"
                        class="block break-all text-sm font-bold text-emerald-600 dark:text-emerald-400 hover:underline underline-offset-2"
                    >
                        {{ publicUrlPreview }}
                    </a>
                    <p v-else class="text-xs font-medium text-slate-400 dark:text-slate-500 italic">Generate or enter a slug to preview the Go Link.</p>
                </div>

                <!-- QR Code Generator Card -->
                <div class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-5 shadow-sm">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div>
                            <p class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                <QrCode class="w-3.5 h-3.5" /> QR Code Generator
                            </p>
                            <p class="mt-0.5 text-xs font-medium text-slate-400 dark:text-slate-500">Rendered from the public Go Link URL.</p>
                        </div>
                        <button
                            type="button"
                            :disabled="!publicUrlPreview"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3 py-2 text-xs font-bold uppercase tracking-widest text-slate-700 dark:text-slate-300 transition-all hover:bg-slate-50 dark:hover:bg-slate-700 shadow-sm disabled:cursor-not-allowed disabled:opacity-50 active:scale-95 shrink-0"
                            @click="downloadQrCode"
                        >
                            <Download class="w-3.5 h-3.5" /> Download QR
                        </button>
                    </div>

                    <div
                        ref="qrWrapper"
                        class="flex min-h-64 items-center justify-center rounded-xl border border-dashed border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/50 p-4 shadow-sm"
                    >
                        <qrcode-vue
                            v-if="publicUrlPreview"
                            :id="qrCanvasId"
                            :value="publicUrlPreview"
                            :size="220"
                            level="M"
                            render-as="canvas"
                        />
                        <p v-else class="text-xs font-medium text-slate-400 dark:text-slate-500 italic text-center">QR preview will appear here once a slug is available.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex flex-col sm:flex-row flex-wrap items-center justify-end gap-3 border-t border-slate-100 dark:border-slate-800/60 pt-6">
            <Link
                :href="route('golinks.index')"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-slate-50 hover:bg-slate-100 dark:bg-slate-800/50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-semibold px-5 py-2.5 text-sm shadow-sm transition-all active:scale-95"
            >
                <X class="w-4 h-4" /> Cancel
            </Link>
            
            <button
                type="button"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-slate-50 hover:bg-slate-100 dark:bg-slate-800/50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-semibold px-5 py-2.5 text-sm shadow-sm transition-all active:scale-95"
                @click="resetToSource"
            >
                <RotateCcw class="w-4 h-4" /> Reset
            </button>
            
            <button
                v-if="isEdit"
                type="button"
                :disabled="processing"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10 px-5 py-2.5 text-sm font-bold text-rose-700 dark:text-rose-400 shadow-sm transition-all hover:bg-rose-100 dark:hover:bg-rose-500/20 active:scale-95 disabled:opacity-60 disabled:pointer-events-none"
                @click="handleDelete"
            >
                <Trash2 class="w-4 h-4" /> Delete
            </button>
            
            <button
                :disabled="processing"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-2.5 text-sm shadow-sm transition-all active:scale-95 disabled:opacity-70 disabled:pointer-events-none"
            >
                <Loader2 v-if="processing" class="w-4 h-4 animate-spin" />
                <Save v-else class="w-4 h-4" />
                {{ submitLabel }}
            </button>
        </div>
    </form>
</template>