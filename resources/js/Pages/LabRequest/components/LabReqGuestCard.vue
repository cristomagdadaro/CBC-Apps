<script>
import PreregistrationCard from "@/Pages/Forms/components/PreregistrationCard.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import DataFormatterMixin from "@/Modules/mixins/DataFormatterMixin";
import RequesterGuestCard from "@/Pages/LabRequest/components/RequesterGuestCard.vue";

export default {
    name: "LabReqGuestCard",
    components: {RequesterGuestCard, InputError, InputLabel, TextInput, PreregistrationCard},
    props: {
        data: { type: Object },
    },
    mixins: [DataFormatterMixin],
}
</script>

<template>
    <div class="border p-2 md:rounded-md flex flex-col gap-2 bg-gray-100 max-w-xl drop-shadow-lg">
        <requester-guest-card @createdModel="$emit('createdModel', $event)" />
    </div>
</template>

<style scoped>

</style>
