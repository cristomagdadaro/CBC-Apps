<script>
import UseRequestCard from "@/Pages/LabRequest/components/UseRequestCard.vue";

export default {
    name: "ListOfUseRequests",
    components: { UseRequestCard },
    props: {
        formsData: Array,
    },
}
</script>

<template>
    <div class="grid xl:grid-cols-3  lg:grid-cols-2 grid-flow-row gap-2 p-2">
        <use-request-card
            v-for="data in formsData"
            :key="data.id"
            :data="data"
            @deletedModel="$emit('removeModel', $event)"
            @updated="$emit('updated', $event)"
        />
    </div>
</template>

<style scoped>

</style>
