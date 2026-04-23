<script>
import {Head} from "@inertiajs/vue3";
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
}
</script>

<template>
    <Head title="Go Links" />

    <AppLayout>
        <template #header>
            <go-links-header-actions />
        </template>

        <CRCMDatatable :base-model="GoLink" :can-view="true" :can-create="true" :can-update="true" :can-delete="true">
            <template #cell-public_url="{ row, value }">
                <a :href="value" target="_blank" rel="noopener" class="text-emerald-700 underline underline-offset-2">
                    {{ value || row.public_url }}
                </a>
            </template>
            <template #cell-target_url="{ value }">
                <a :href="value" target="_blank" rel="noopener" class="text-slate-700 underline underline-offset-2">
                    {{ value }}
                </a>
            </template>
            <template #cell-status="{ value }">
                <span :class="value ? 'text-emerald-700 font-semibold' : 'text-red-700 font-semibold'">
                    {{ value ? 'Active' : 'Inactive' }}
                </span>
            </template>
            <template #cell-is_public="{ value }">
                {{ value ? 'Yes' : 'No' }}
            </template>
        </CRCMDatatable>
    </AppLayout>
</template>
