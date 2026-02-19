<script>
export default {
    name: "DeleteConfirmationModal",
    props: {
        show: {
            type: Boolean,
            default: false,
        },
        isProcessing: {
            type: Boolean,
            default: false,
        },
        title: {
            type: String,
            default: "Confirm Delete",
        },
        message: {
            type: String,
            default: "Are you sure you want to delete this record? This action cannot be undone.",
        },
        itemName: {
            type: String,
            default: null,
        },
    },
    emits: ['confirm', 'close'],
    methods: {
        handleConfirm() {
            this.$emit('confirm');
        },
        handleClose() {
            this.$emit('close');
        },
    },
};
</script>

<template>
    <confirmation-modal :show="show" @close="handleClose">
        <template #title>
            {{ title }}
        </template>

        <template #content>
            <div class="flex flex-col gap-2">
                <p>{{ message }}</p>
                <p v-if="itemName" class="font-semibold text-gray-700">
                    {{ itemName }}
                </p>
            </div>
        </template>

        <template #footer>
            <div class="flex justify-between w-full">
                <delete-btn 
                    @click="handleConfirm" 
                    :disabled="isProcessing"
                    :class="{'animate-pulse': isProcessing}"
                >
                    <div class="flex gap-1 items-center">
                        <loader-icon v-if="isProcessing" />
                        <span v-if="!isProcessing">Confirm Delete</span>
                        <span v-else>Deleting...</span>
                    </div>
                </delete-btn>
                <cancel-btn @click="handleClose" :disabled="isProcessing">
                    Cancel
                </cancel-btn>
            </div>
        </template>
    </confirmation-modal>
</template>

<style scoped>
</style>
