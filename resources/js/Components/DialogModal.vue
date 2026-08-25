<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from "vue";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: "2xl",
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["close"]);
const showSlot = ref(props.show);

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = "hidden";
            showSlot.value = true;
        } else {
            document.body.style.overflow = null;
            setTimeout(() => {
                showSlot.value = false;
            }, 200);
        }
    },
);

const close = () => {
    if (props.closeable) {
        emit("close");
    }
};

const closeOnEscape = (e) => {
    if (e.key === "Escape" && props.show) {
        close();
    }
};

onMounted(() => document.addEventListener("keydown", closeOnEscape));

onUnmounted(() => {
    document.removeEventListener("keydown", closeOnEscape);
    document.body.style.overflow = null;
});

const maxWidthClass = computed(() => {
    return {
        sm: "sm:max-w-sm",
        md: "sm:max-w-md",
        lg: "sm:max-w-lg",
        xl: "sm:max-w-xl",
        "2xl": "sm:max-w-2xl",
    }[props.maxWidth];
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="showSlot"
            class="fixed inset-0 z-50 m-0 min-h-full min-w-full overflow-y-auto bg-transparent"
            scroll-region>
            <div class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0">
                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0">
                    <div
                        v-show="show"
                        class="fixed inset-0 transform transition-all"
                        @click="close">
                        <div class="absolute inset-0 bg-gray-500 opacity-75 dark:bg-gray-900" />
                    </div>
                </transition>

                <transition
                    enter-active-class="ease-out duration-300"
                    enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    enter-to-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-active-class="ease-in duration-200"
                    leave-from-class="opacity-100 translate-y-0 sm:scale-100"
                    leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                    <div
                        v-show="show"
                        class="mb-6 transform rounded-xl bg-white shadow-xl transition-all sm:mx-auto sm:w-full dark:bg-gray-800"
                        :class="maxWidthClass">
                        <div class="px-6 py-4">
                            <div class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                <slot name="title" />
                            </div>

                            <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                                <slot name="content" />
                            </div>
                        </div>

                        <div class="flex flex-row justify-end bg-gray-100 px-6 py-4 text-end dark:bg-gray-800">
                            <slot name="footer" />
                        </div>
                    </div>
                </transition>
            </div>
        </div>
    </Teleport>
</template>
