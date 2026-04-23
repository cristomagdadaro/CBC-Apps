<script>
import { Link, router } from "@inertiajs/vue3";
import QrcodeVue from "qrcode.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DtoResponse from "@/Modules/dto/DtoResponse";
import GoLink from "@/Modules/domain/GoLink";

export default {
    name: "GoLinkForm",
    components: { Link, QrcodeVue },
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
    <form v-if="form" @submit.prevent="submitProxy" class="mx-auto max-w-5xl p-4">
        <section class="space-y-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="border-b border-slate-200 pb-5">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Go Link</p>
                <h2 class="mt-2 text-2xl font-semibold text-slate-900">{{ formTitle }}</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                    {{ formDescription }}
                </p>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="space-y-4">
                    <text-input
                        required
                        label="Target URL"
                        v-model="form.target_url"
                        :error="form.errors.target_url"
                        guide="Destination must be a full URL."
                    />

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <label class="mb-2 block text-sm font-semibold text-slate-900">Slug</label>
                        <div class="flex gap-2">
                            <input
                                v-model="form.slug"
                                type="text"
                                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                                placeholder="Leave empty to auto-generate"
                            >
                            <button
                                type="button"
                                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                @click="generateSlug"
                            >
                                Generate
                            </button>
                        </div>
                        <p v-if="form.errors.slug" class="mt-2 text-sm text-red-600">{{ form.errors.slug }}</p>
                        <p class="mt-2 text-xs text-slate-500">
                            Preview: {{ publicUrlPreview || `${publicBaseUrl}/go/[auto-generated]` }}
                        </p>
                    </div>

                    <text-input
                        label="Expires"
                        type="datetime-local"
                        v-model="form.expires"
                        :error="form.errors.expires"
                    />

                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <input
                                v-model="form.status"
                                type="checkbox"
                                class="mt-1 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                            />
                            <span>
                                <span class="block text-sm font-semibold text-slate-900">Active</span>
                                <span class="mt-1 block text-sm text-slate-600">
                                    Inactive links remain stored but stop redirecting.
                                </span>
                            </span>
                        </label>
                        <label class="flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <input
                                v-model="form.is_public"
                                type="checkbox"
                                class="mt-1 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                            />
                            <span>
                                <span class="block text-sm font-semibold text-slate-900">Public Submission</span>
                                <span class="mt-1 block text-sm text-slate-600">
                                    Flag links that originate from a public flow.
                                </span>
                            </span>
                        </label>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <text-input label="OG Title" v-model="form.og_title" :error="form.errors.og_title" />
                        <text-input label="OG Image URL" v-model="form.og_image" :error="form.errors.og_image" />
                    </div>

                    <text-area label="OG Description" v-model="form.og_description" :error="form.errors.og_description" />
                </div>

                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <p class="text-sm font-semibold text-slate-900">Public Link</p>
                        <a
                            v-if="publicUrlPreview"
                            :href="publicUrlPreview"
                            target="_blank"
                            rel="noopener"
                            class="mt-2 block break-all text-sm text-emerald-700 underline underline-offset-2"
                        >
                            {{ publicUrlPreview }}
                        </a>
                        <p v-else class="mt-2 text-sm text-slate-500">Generate or enter a slug to preview the Go Link.</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">QR Code</p>
                                <p class="mt-1 text-xs text-slate-500">Rendered from the public Go Link URL.</p>
                            </div>
                            <button
                                type="button"
                                :disabled="!publicUrlPreview"
                                class="rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                                @click="downloadQrCode"
                            >
                                Download QR
                            </button>
                        </div>

                        <div
                            ref="qrWrapper"
                            class="mt-4 flex min-h-72 items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white p-4"
                        >
                            <qrcode-vue
                                v-if="publicUrlPreview"
                                :id="qrCanvasId"
                                :value="publicUrlPreview"
                                :size="240"
                                level="M"
                                render-as="canvas"
                            />
                            <p v-else class="text-sm text-slate-500">QR preview will appear here once a slug is available.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 pt-6">
                <Link
                    :href="route('golinks.index')"
                    class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                >
                    Cancel
                </Link>
                <button
                    type="button"
                    class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    @click="resetToSource"
                >
                    Reset
                </button>
                <button
                    v-if="isEdit"
                    type="button"
                    :disabled="processing"
                    class="rounded-xl border border-red-300 px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-50 disabled:opacity-50"
                    @click="handleDelete"
                >
                    Delete
                </button>
                <button
                    :disabled="processing"
                    class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 disabled:opacity-50"
                >
                    {{ submitLabel }}
                </button>
            </div>
        </section>
    </form>
</template>
