<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Form from "@/Modules/domain/Form";

export default {
    name: "SuspendFormBtn",
    props: {
        data: Object
    },
    mixins: [ApiMixin],
    beforeMount() {
        this.model = new Form();
        this.setFormAction('update');
    },
    methods: {
        async handleUpdateSuspended() {
            this.form.is_suspended = !this.form.is_suspended;
            const response = await this.submitUpdate();
            this.form.is_suspended = response.data.is_suspended;
            this.$emit("updated", response);
        }
    }
}
</script>

<template>
    <form  v-if="!!form" @submit.prevent="handleUpdateSuspended">
        <button v-if="model.processing && form.is_suspended" @click.prevent="handleUpdateSuspended" :class=" form.is_suspended ? 'bg-yellow-200' : 'bg-yellow-400'" :disabled="model.processing" class="disabled:bg-opacity-50 bg-yellow-200 text-yellow-900 w-fit px-2 py-1 rounded" title="Temporarily stop accepting responses">
            Closing form
        </button>
        <button v-else-if="model.processing && !form.is_suspended" @click.prevent="handleUpdateSuspended" :class=" form.is_suspended ? 'bg-yellow-200' : 'bg-yellow-400'" :disabled="model.processing" class="disabled:bg-opacity-50 bg-yellow-200 text-yellow-900 w-fit px-2 py-1 rounded" title="Temporarily stop accepting responses">
            Opening form
        </button>
        <button v-else-if="!form.is_suspended" @click.prevent="handleUpdateSuspended" :class=" form.is_suspended ? 'bg-yellow-200' : 'bg-yellow-400'" class="disabled:bg-opacity-50 bg-yellow-200 text-yellow-900 w-fit px-2 py-1 rounded" title="Temporarily stop accepting responses">
            Close
        </button>
        <button v-else @click.prevent="handleUpdateSuspended" :class=" form.is_suspended ? 'bg-yellow-200' : 'bg-yellow-400'" class="disabled:bg-opacity-50  text-yellow-900 w-fit px-2 py-1 rounded" title="Temporarily stop accepting responses">
            Open
        </button>
    </form>
</template>

<style scoped>

</style>
