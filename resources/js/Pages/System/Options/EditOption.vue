<script>
import OptionsForm from './components/OptionsForm.vue';

export default {
  name: "EditOption",
  components: { OptionsForm },
  props: {
    data: {
      type: Object,
      required: true
    }
  }
};
</script>

<template>
  <app-layout title="Edit Option">
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <Link
            :href="route('system.options.index')"
            class="font-bold text-xl text-gray-800 dark:text-gray-200 leading-tight"
          >
            Edit Option
          </Link>
          <p class="text-sm text-gray-600">Modify an existing system option or configuration</p>
        </div>
      </div>
    </template>
    <div class="min-h-screen bg-gray-50">
      <!-- Form -->
      <div class="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8">
        <OptionsForm :data="data" />
      </div>
    </div>
  </app-layout>
</template>
