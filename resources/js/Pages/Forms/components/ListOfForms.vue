<script>
import EventCard from "@/Pages/Forms/components/EventCard.vue";

export default {
    name: "ListOfForms",
    components: { EventCard },
    props: {
        formsData: Array,
    },
    methods: {
        handleDelete(deletedModel) {
            this.$emit("removeModel", deletedModel);
        },
    },
};
</script>

<template>
    <div class="grid lg:grid-cols-3 md:grid-cols-1 grid-cols-1 gap-2 p-2">
        <event-card
            v-for="data in formsData"
            :key="data.id"
            :data="data"
            @deletedModel="handleDelete"
        />
    </div>
</template>
