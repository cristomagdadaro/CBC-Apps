<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Form from "@/Modules/domain/Form";
import DtoError from "@/Modules/dto/DtoError";
import LoaderIcon from '@/Components/Icons/LoaderIcon.vue';

export default {
  components: { LoaderIcon },
    name: "SuspendFormBtn",
    props: {
        data: Object
    },
    mixins: [ApiMixin],
    data() {
        return {
            showConfirm: false
        };
    },
    computed: {
        isSuspended() {
            return this.form?.is_suspended || false;
        },
        buttonLabel() {
            if (this.model?.api?.processing) {
                return !this.isSuspended ? 'Opening...' : 'Closing...';
            }
            return this.isSuspended ? 'Reopen Form' : 'Close Form';
        },
        buttonTooltip() {
            return this.isSuspended 
                ? 'Reopen this form to accept new responses'
                : 'Temporarily stop accepting new responses';
        }
    },
    beforeMount() {
        this.model = new Form();
        this.setFormAction('update');
    },
    methods: {
        async handleUpdateSuspended() {
            this.showConfirm = false;
            this.form.is_suspended = !this.form.is_suspended;
            this.form.requirements = [];
            const response = await this.submitUpdate();
            if(!(response instanceof DtoError)) {
                this.form.is_suspended = response.data.is_suspended;
                this.$emit("updated", response);
            } else {
                this.$emit("failedUpdate", response);
            }
        }
    }
}
</script>

<template>
    <div v-if="form" class="flex items-center">
        <!-- Main Toggle Button -->
        <button
            @click.prevent="showConfirm = true"
            :disabled="model.api.processing"
            :title="buttonTooltip"
            :class="{
                'bg-red-500 hover:bg-red-600 text-white': !isSuspended && !model.api.processing,
                'bg-green-500 hover:bg-green-600 text-white': isSuspended && !model.api.processing,
                'bg-gray-400 text-gray-600 cursor-not-allowed': model.api.processing
            }"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-md shadow-sm font-medium text-sm transition ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 hover:scale-110 duration-200"
            :focus-ring-color="isSuspended ? 'focus:ring-green-400' : 'focus:ring-red-400'"
        >
            <!-- Icon -->
            <svg 
                v-if="!model.api.processing"
                xmlns="http://www.w3.org/2000/svg" 
                class="h-4 w-4" 
                viewBox="0 0 24 24" 
                fill="none" 
                stroke="currentColor" 
                stroke-width="2" 
                stroke-linecap="round" 
                stroke-linejoin="round"
            >
                <g v-if="!isSuspended">
                    <!-- Lock icon (closed) -->
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </g>
                <g v-else>
                    <!-- Unlock icon (open) -->
                    <path d="M7 11V7a5 5 0 0 1 9.2 1"></path>
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                </g>
            </svg>

            <!-- Loading Spinner -->
            <loader-icon v-else />

            <!-- Label -->
            <span class="hidden">{{ buttonLabel }}</span>
        </button>

        <!-- Confirmation Dialog -->
        <transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div 
                v-if="showConfirm" 
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-25"
                @click="showConfirm = false"
            >
                <div 
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-sm mx-4"
                    @click.stop
                >
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                        {{ isSuspended ? 'Reopen Form?' : 'Close Form?' }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-6">
                        {{ isSuspended 
                            ? 'This will allow users to submit new responses to this form.' 
                            : 'This will prevent users from submitting new responses to this form.' 
                        }}
                    </p>

                    <!-- Buttons -->
                    <div class="flex gap-3 justify-end">
                        <button
                            @click="showConfirm = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 rounded-md transition"
                        >
                            Cancel
                        </button>
                        <button
                            @click="handleUpdateSuspended"
                            :disabled="model.api.processing"
                            :class="{
                                'bg-red-500 hover:bg-red-600 text-white': !isSuspended,
                                'bg-green-500 hover:bg-green-600 text-white': isSuspended,
                                'opacity-50 cursor-not-allowed': model.api.processing
                            }"
                            class="px-4 py-2 text-sm font-medium rounded-md transition"
                        >
                            {{ isSuspended ? 'Reopen' : 'Close' }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>
