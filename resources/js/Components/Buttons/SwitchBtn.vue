<script>
export default {
  name: "SwitchBtn",
  props: {
    modelValue: {
      type: Boolean,
      default: false,
    },
    label: {
      type: String,
      default: null,
    },
  },
  emits: ["update:modelValue", "toggled"],
  data() {
    return {
      isTransitioning: false,
    };
  },
  methods: {
    handleToggle() {
      this.isTransitioning = true;
      this.$emit("update:modelValue", !this.modelValue);
      this.$emit("toggled", !this.modelValue);
      setTimeout(() => {
        this.isTransitioning = false;
      }, 300);
    },
  },
};
</script>

<template>
  <button
    type="button"
    class="switch-button inline-flex items-center px-2 py-1 border-2 border-gray-300 dark:border-gray-600 text-sm leading-4 font-medium rounded-full transition ease-in-out duration-5000"
    :class="{
      'bg-blue-100 dark:bg-blue-900 border-blue-400 dark:border-blue-600 text-blue-600 dark:text-blue-300': modelValue,
      'bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400': !modelValue,
    }"
    @click="handleToggle"
    :title="label || (modelValue ? 'Enabled' : 'Disabled')"
  >
    <Transition name="switch-icon" mode="out-in">
      <div :key="modelValue" class="flex items-center justify-center">
        <template v-if="modelValue">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="18"
            height="18"
            fill="currentColor"
            class="bi bi-toggle-on icon-toggle"
            viewBox="0 0 16 16"
          >
            <path
              d="M5 3a5 5 0 0 0 0 10h6a5 5 0 0 0 0-10zm6 9a4 4 0 1 1 0-8 4 4 0 0 1 0 8"
            />
          </svg>
        </template>
        <template v-else>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="18"
            height="18"
            fill="currentColor"
            class="bi bi-toggle-off icon-toggle"
            viewBox="0 0 16 16"
          >
            <path
              d="M11 4a4 4 0 0 1 0 8H8a5 5 0 0 0 2-4 5 5 0 0 0-2-4zm-6 8a4 4 0 1 1 0-8 4 4 0 0 1 0 8M0 8a5 5 0 0 0 5 5h6a5 5 0 0 0 0-10H5a5 5 0 0 0-5 5"
            />
          </svg>
        </template>
      </div>
    </Transition>
    <span v-if="label" class="ms-2">{{ label }}</span>
  </button>
</template>

<style scoped>
.switch-button {
  position: relative;
  overflow: hidden;
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

.switch-button:hover {
  transform: scale(1.05);
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.15), 0 2px 4px rgba(0, 0, 0, 0.1);
}

.switch-button:active {
  transform: scale(0.98);
}

.switch-button .icon-toggle {
  display: inline-block;
  transition: transform 0.2s ease;
}

/* Icon transition animations */
.switch-icon-enter-active,
.switch-icon-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.switch-icon-enter-from {
  opacity: 0;
  transform: rotateY(-180deg) scale(0.8);
}

.switch-icon-leave-to {
  opacity: 0;
  transform: rotateY(180deg) scale(0.8);
}

.switch-icon-enter-to,
.switch-icon-leave-from {
  opacity: 1;
  transform: rotateY(0) scale(1);
}
</style>
