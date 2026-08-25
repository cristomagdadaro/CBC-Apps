<script>
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Form from "@/Modules/domain/Form";
import DtoError from "@/Modules/dto/DtoError";

export default {
    name: "SuspendFormBtn",
    emits: ["updated", "failedUpdate"],
    props: {
        data: Object,
    },
    mixins: [ApiMixin],
    data() {
        return {
            showConfirm: false,
        };
    },
    computed: {
        isSuspended() {
            return this.form?.is_suspended || false;
        },
        buttonLabel() {
            if (this.model?.api?.processing) {
                return !this.isSuspended ? "Opening..." : "Closing...";
            }
            return this.isSuspended ? "Reopen Form" : "Close Form";
        },
        buttonTooltip() {
            return this.isSuspended ? "Reopen this form to accept new responses" : "Temporarily stop accepting new responses";
        },
        buttonConfig() {
            if (this.isSuspended) {
                return {
                    bg: "bg-emerald-500 hover:bg-emerald-600 dark:bg-emerald-600 dark:hover:bg-emerald-700",
                    ring: "focus:ring-emerald-500",
                    icon: "LuUnlock",
                    label: "Reopen",
                    confirmTitle: "Reopen Form?",
                    confirmText: "This will allow users to submit new responses to this form.",
                    confirmBtn: "bg-emerald-500 hover:bg-emerald-600 text-white",
                };
            }
            return {
                bg: "bg-amber-500 hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700",
                ring: "focus:ring-amber-500",
                icon: "LuLock",
                label: "Close",
                confirmTitle: "Close Form?",
                confirmText: "This will prevent users from submitting new responses to this form.",
                confirmBtn: "bg-amber-500 hover:bg-amber-600 text-white",
            };
        },
    },
    beforeMount() {
        this.model = new Form();
        this.setFormAction("update");
    },
    methods: {
        async handleUpdateSuspended() {
            this.showConfirm = false;
            this.form.is_suspended = !this.form.is_suspended;
            const response = await this.submitUpdate();
            if (!(response instanceof DtoError)) {
                this.form.is_suspended = response.data.is_suspended;
                this.$emit("updated", response);
            } else {
                this.form.is_suspended = !this.form.is_suspended;
                this.$emit("failedUpdate", response);
            }
        },
    },
};
</script>

<template>
    <div
        v-if="form"
        class="relative">
        <!-- Main Toggle Button -->
        <button
            @click.prevent="showConfirm = true"
            :disabled="model.api.processing"
            :title="buttonTooltip"
            class="group relative inline-flex h-10 w-10 items-center justify-center rounded-xl shadow-sm transition-all duration-200 ease-out hover:scale-105 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
            :class="[buttonConfig.bg, buttonConfig.ring]">
            <!-- Icon -->
            <Transition
                mode="out-in"
                enter-active-class="transition duration-150 ease-out"
                enter-from-class="opacity-0 scale-50"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-50">
                <component
                    :is="buttonConfig.icon"
                    v-if="!model.api.processing"
                    :key="isSuspended"
                    class="h-5 w-5 text-white" />
                <LuLoader2
                    v-else
                    class="h-5 w-5 animate-spin text-white" />
            </Transition>

            <!-- Status Indicator Dot -->
            <span
                class="absolute -right-1 -top-1 flex h-3 w-3"
                :class="isSuspended ? '' : 'hidden'">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-500"></span>
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
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
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
                            class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-800">
                            <!-- Header -->
                            <div class="flex items-center gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                                <div
                                    class="rounded-xl p-2"
                                    :class="isSuspended ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400'">
                                    <component
                                        :is="buttonConfig.icon"
                                        class="h-5 w-5" />
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ buttonConfig.confirmTitle }}
                                </h3>
                            </div>

                            <!-- Content -->
                            <div class="px-6 py-4">
                                <p class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                                    {{ buttonConfig.confirmText }}
                                </p>

                                <!-- Current Status Badge -->
                                <div class="mt-4 flex items-center gap-2 text-xs">
                                    <span class="text-gray-500 dark:text-gray-400">Current status:</span>
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 font-medium"
                                        :class="isSuspended ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'">
                                        <span
                                            class="h-1.5 w-1.5 rounded-full"
                                            :class="isSuspended ? 'bg-emerald-500' : 'bg-green-500'"></span>
                                        {{ isSuspended ? "Closed" : "Open" }}
                                    </span>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="flex justify-end gap-3 bg-gray-50 px-6 py-4 dark:bg-gray-700/30">
                                <button
                                    @click="showConfirm = false"
                                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                                    Cancel
                                </button>
                                <button
                                    @click="handleUpdateSuspended"
                                    :disabled="model.api.processing"
                                    class="rounded-lg px-4 py-2 text-sm font-medium shadow-sm transition-all hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                                    :class="[buttonConfig.confirmBtn, buttonConfig.ring]">
                                    <span
                                        v-if="model.api.processing"
                                        class="flex items-center gap-2">
                                        <LuLoader2 class="h-4 w-4 animate-spin" />
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
