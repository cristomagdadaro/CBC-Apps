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
        confirmButtonLabel: {
            type: String,
            default: 'Confirm Delete',
        },
        permanentDeleteEnabled: {
            type: Boolean,
            default: false,
        },
        permanentDeleteLabel: {
            type: String,
            default: 'Permanent Delete',
        },
        permanentDeletePrompt: {
            type: String,
            default: null,
        },
        permanentDeleteTarget: {
            type: String,
            default: '',
        },
        confirmationError: {
            type: String,
            default: null,
        },
    },
    emits: ['confirm', 'confirm-permanent', 'close'],
    data() {
        return {
            permanentDeleteInput: '',
        };
    },
    computed: {
        canConfirmPermanent() {
            if (!this.permanentDeleteEnabled) {
                return false;
            }

            return this.permanentDeleteInput.trim() === this.permanentDeleteTarget;
        },
    },
    watch: {
        show(value) {
            if (!value) {
                this.permanentDeleteInput = '';
            }
        },
    },
    methods: {
        handleConfirm() {
            this.$emit('confirm');
        },
        handlePermanentConfirm() {
            this.$emit('confirm-permanent', this.permanentDeleteInput.trim());
        },
        handleClose() {
            this.permanentDeleteInput = '';
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
                <div v-if="permanentDeleteEnabled" class="mt-3 rounded border border-red-200 bg-red-50 p-3">
                    <p class="text-sm text-red-700">
                        {{ permanentDeletePrompt }}
                    </p>
                    <text-input
                        class="mt-2"
                        label="CBC Barcode Confirmation"
                        :model-value="permanentDeleteInput"
                        @update:model-value="permanentDeleteInput = $event"
                        :placeholder="permanentDeleteTarget"
                    />
                    <p v-if="confirmationError" class="text-sm text-red-600">
                        {{ confirmationError }}
                    </p>
                </div>
            </div>
        </template>

        <template #footer>
            <div class="flex justify-between w-full">
                <div class="flex gap-2">
                    <delete-btn 
                        @click="handleConfirm" 
                        :disabled="isProcessing"
                        :class="{'animate-pulse': isProcessing}"
                    >
                        <div class="flex gap-1 items-center">
                            <loader-icon v-if="isProcessing" />
                            <span v-if="!isProcessing">{{ confirmButtonLabel }}</span>
                            <span v-else>Deleting...</span>
                        </div>
                    </delete-btn>
                    <delete-btn
                        v-if="permanentDeleteEnabled"
                        @click="handlePermanentConfirm"
                        :disabled="isProcessing || !canConfirmPermanent"
                        class="bg-red-700 text-white hover:bg-red-800 disabled:opacity-50"
                    >
                        {{ permanentDeleteLabel }}
                    </delete-btn>
                </div>
                <cancel-btn @click="handleClose" :disabled="isProcessing">
                    Cancel
                </cancel-btn>
            </div>
        </template>
    </confirmation-modal>
</template>

<style scoped>
</style>
