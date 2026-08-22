<script>
import GoLinksHeaderActions from "@/Pages/GoLinks/components/GoLinksHeaderActions.vue";
import GoLinkForm from "@/Pages/GoLinks/components/GoLinkForm.vue";

export default {
    name: "EditGoLinkForm",
    components: { GoLinksHeaderActions, GoLinkForm },
};
</script>

<template>
    <AppLayout title="Edit Go Link">
        <template #header>
            <go-links-header-actions />
        </template>

        <div class="default-container pt-5">
            <go-link-form :data="$page.props.data" />
        </div>
    </AppLayout>
</template>