<script>
import PersonnelCard from "@/Pages/Inventory/Personnel/components/PersonnelCard.vue";

export default {
    name: "PersonnelList",
    components: {PersonnelCard},
    props: {
        apiResponse: {
            type: Object,
        }
    }
}
</script>

<template>
    <div v-if="apiResponse" class="flex flex-wrap gap-2">
        <personnel-card v-for="data in apiResponse.data" :key="data.id" :data="data" />
    </div>
</template>

<style scoped>

</style>
