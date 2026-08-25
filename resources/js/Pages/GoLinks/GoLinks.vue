<script>
import { Head } from "@inertiajs/vue3";
import CRCMDatatable from "@/Components/CRCMDatatable/CRCMDatatable.vue";
import GoLink from "@/Modules/domain/GoLink";
import GoLinksHeaderActions from "@/Pages/GoLinks/components/GoLinksHeaderActions.vue";

export default {
    name: "GoLinks",
    components: {
        CRCMDatatable,
        GoLinksHeaderActions,
        Head,
    },
    computed: {
        GoLink() {
            return GoLink;
        },
    },
};
</script>

<template>
    <Head title="Go Links" />

    <AppLayout>
        <template #header>
            <go-links-header-actions />
        </template>

        <div class="default-container pt-5">
            <div class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white/80 p-3 shadow-sm backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/80">
                <CRCMDatatable
                    :base-model="GoLink"
                    :can-view="true"
                    :can-create="true"
                    :can-update="true"
                    :can-delete="true">
                    <template #cell-public_url="{ row, value }">
                        <a
                            :href="value"
                            target="_blank"
                            rel="noopener"
                            class="font-medium text-emerald-600 underline-offset-2 hover:underline dark:text-emerald-400">
                            {{ value || row.public_url }}
                        </a>
                    </template>
                    <template #cell-target_url="{ value }">
                        <a
                            :href="value"
                            target="_blank"
                            rel="noopener"
                            class="block max-w-xs truncate font-medium text-slate-600 underline-offset-2 hover:underline dark:text-slate-300">
                            {{ value }}
                        </a>
                    </template>
                    <template #cell-status="{ value }">
                        <span
                            class="inline-flex items-center rounded-md px-2 py-0.5 text-[0.6rem] font-bold uppercase tracking-widest shadow-sm"
                            :class="value ? 'border border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400' : 'border border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-400'">
                            {{ value ? "Active" : "Inactive" }}
                        </span>
                    </template>
                    <template #cell-is_public="{ value }">
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">
                            {{ value ? "Yes" : "No" }}
                        </span>
                    </template>
                </CRCMDatatable>
            </div>
        </div>
    </AppLayout>
</template>
