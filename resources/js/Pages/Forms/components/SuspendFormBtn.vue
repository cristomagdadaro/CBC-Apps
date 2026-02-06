<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Form from "@/Modules/domain/Form";
import DtoError from "@/Modules/dto/DtoError";

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
            this.form.requirements = [];
            const response = await this.submitUpdate();
            if(!(response instanceof DtoError)) {
                this.form.is_suspended = response.data.is_suspended;
                this.$emit("updated", response);
            }else {
                this.$emit("failedUpdate", response);
            }
        }
    }
}
</script>

<template>
    <div class="flex">
        <!-- Close Form -->
        <form v-if="!!form"
            @submit.prevent="handleUpdateSuspended"
            :class="[ 'flex items-center gap-1 text-yellow-900 w-fit px-2 py-1 rounded-l transition', (form.is_suspended || model.api.processing) ? 'bg-gray-300 text-gray-500 cursor-not-allowed' : 'bg-yellow-400']"
            title="Temporarily stop accepting responses">
            <button
                type="submit"
                :disabled="form.is_suspended || model.api.processing"
                class="disabled:cursor-not-allowed"
            >
                <span v-if="model.api.processing && form.is_suspended">Closing...</span>
                <span v-else>Close</span>
            </button>
        </form>

        <!-- Open Form -->
        <form v-if="!!form"
            @submit.prevent="handleUpdateSuspended"
            :class="['flex items-center gap-1 text-green-900 w-fit px-2 py-1 rounded-r transition',(!form.is_suspended || model.api.processing)? 'bg-gray-300 text-gray-500 cursor-not-allowed': 'bg-green-400']"
            title="Reopen to accept responses">
            <button
                type="submit"
                :disabled="!form.is_suspended || model.api.processing"
                class="disabled:cursor-not-allowed"
            >
                <span v-if="model.api.processing && !form.is_suspended">Opening...</span>
                <span v-else>Open</span>
            </button>
        </form>
    </div>

</template>

<style scoped>

</style>
