<script>
import GoLinksHeaderActions from "@/Pages/GoLinks/components/GoLinksHeaderActions.vue";
import GoLinkForm from "@/Pages/GoLinks/components/GoLinkForm.vue";

export default {
    name: "CreateGoLinkForm",
    components: { GoLinksHeaderActions, GoLinkForm },
};
</script>

<template>
    <AppLayout title="Create Go Link">
        <template #header>
            <go-links-header-actions />
        </template>

        <div class="default-container pt-5">
            <go-link-form />
        </div>
    </AppLayout>
</template>