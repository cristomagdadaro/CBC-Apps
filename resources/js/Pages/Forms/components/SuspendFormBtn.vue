<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Form from "@/Modules/domain/Form";
import DtoError from "@/Modules/dto/DtoError";

export default {
    name: "SuspendFormBtn",
    emits: ["updated", "failedUpdate"],
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
        },
        buttonConfig() {
            if (this.isSuspended) {
                return {
                    bg: 'bg-emerald-500 hover:bg-emerald-600 dark:bg-emerald-600 dark:hover:bg-emerald-700',
                    ring: 'focus:ring-emerald-500',
                    icon: 'LuUnlock',
                    label: 'Reopen',
                    confirmTitle: 'Reopen Form?',
                    confirmText: 'This will allow users to submit new responses to this form.',
                    confirmBtn: 'bg-emerald-500 hover:bg-emerald-600 text-white'
                };
            }
            return {
                bg: 'bg-amber-500 hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700',
                ring: 'focus:ring-amber-500',
                icon: 'LuLock',
                label: 'Close',
                confirmTitle: 'Close Form?',
                confirmText: 'This will prevent users from submitting new responses to this form.',
                confirmBtn: 'bg-amber-500 hover:bg-amber-600 text-white'
            };
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
            const response = await this.submitUpdate();
            if(!(response instanceof DtoError)) {
                this.form.is_suspended = response.data.is_suspended;
                this.$emit("updated", response);
            } else {
                this.form.is_suspended = !this.form.is_suspended;
                this.$emit("failedUpdate", response);
            }
        }
    }
}
</script>

<template>
    <div v-if="form" class="relative">
        <!-- Main Toggle Button -->
        <button
            @click.prevent="showConfirm = true"
            :disabled="model.api.processing"
            :title="buttonTooltip"
            class="group relative inline-flex items-center justify-center w-10 h-10 rounded-xl shadow-sm transition-all duration-200 ease-out focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-md hover:scale-105 active:scale-95"
            :class="[buttonConfig.bg, buttonConfig.ring]"
        >
            <!-- Icon -->
            <Transition mode="out-in" enter-active-class="transition duration-150 ease-out" enter-from-class="opacity-0 scale-50" enter-to-class="opacity-100 scale-100" leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100 scale-100" leave-to-class="opacity-0 scale-50">
                <component 
                    :is="buttonConfig.icon" 
                    v-if="!model.api.processing" 
                    :key="isSuspended"
                    class="w-5 h-5 text-white"
                />
                <LuLoader2 v-else class="w-5 h-5 text-white animate-spin" />
            </Transition>
            
            <!-- Status Indicator Dot -->
            <span 
                class="absolute -top-1 -right-1 flex h-3 w-3"
                :class="isSuspended ? '' : 'hidden'">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
            </span>
        </button>

        <!-- Confirmation Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0">
                <div 
                    v-if="showConfirm" 
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
                    @click.self="showConfirm = false">
                    
                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 scale-95 translate-y-4"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition ease-in duration-150"
                        leave-from-class="opacity-100 scale-100 translate-y-0"
                        leave-to-class="opacity-0 scale-95 translate-y-4">
                        <div 
                            v-if="showConfirm"
                            class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full overflow-hidden ring-1 ring-black/5">
                            
                            <!-- Header -->
                            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-3">
                                <div 
                                    class="p-2 rounded-xl"
                                    :class="isSuspended ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400'">
                                    <component :is="buttonConfig.icon" class="w-5 h-5" />
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ buttonConfig.confirmTitle }}
                                </h3>
                            </div>

                            <!-- Content -->
                            <div class="px-6 py-4">
                                <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
                                    {{ buttonConfig.confirmText }}
                                </p>
                                
                                <!-- Current Status Badge -->
                                <div class="mt-4 flex items-center gap-2 text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">Current status:</span>
                                    <span 
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-medium"
                                        :class="isSuspended ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300'">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="isSuspended ? 'bg-emerald-500' : 'bg-green-500'"></span>
                                        {{ isSuspended ? 'Closed' : 'Open' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/30 flex gap-3 justify-end">
                                <button
                                    @click="showConfirm = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-600 transition-colors">
                                    Cancel
                                </button>
                                <button
                                    @click="handleUpdateSuspended"
                                    :disabled="model.api.processing"
                                    class="px-4 py-2 text-sm font-medium rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all hover:shadow-md active:scale-95"
                                    :class="[buttonConfig.confirmBtn, buttonConfig.ring]">
                                    <span v-if="model.api.processing" class="flex items-center gap-2">
                                        <LuLoader2 class="w-4 h-4 animate-spin" />
                                        Processing...
                                    </span>
                                    <span v-else>{{ buttonConfig.label }}</span>
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
