<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Form from "@/Modules/domain/Form";
import DtoError from "@/Modules/dto/DtoError.js";

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
    <form  v-if="!!form" @submit.prevent="handleUpdateSuspended" :class=" form.is_suspended ? 'bg-yellow-200' : 'bg-yellow-400'" class="disabled:bg-opacity-50 text-yellow-900 w-fit px-2 py-1 rounded flex" title="Temporarily stop accepting responses" >
        <button v-if="model.processing && form.is_suspended" @click.prevent="handleUpdateSuspended" :disabled="model.processing">
            Closing form
        </button>
        <button v-else-if="model.processing && !form.is_suspended" @click.prevent="handleUpdateSuspended" :disabled="model.processing">
            Opening form
        </button>
        <button v-else-if="!form.is_suspended" @click.prevent="handleUpdateSuspended">
            Close
        </button>
        <button v-else @click.prevent="handleUpdateSuspended">
            Open
        </button>
    </form>
</template>

<style scoped>

</style>
