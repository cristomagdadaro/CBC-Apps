<script>
import OptionsForm from "./components/OptionsForm.vue";

export default {
  name: "OptionUpsert",
  components: {
    OptionsForm,
  },
  props: {
    data: {
      type: Object,
      required: false,
      default: null,
    },
  },
  computed: {
    isEdit() {
      return !!this.data;
    },
    pageTitle() {
      return this.isEdit ? "Edit Option" : "Create Option";
    },
    pageSubtitle() {
      return this.isEdit
        ? "Update an existing option without leaving the standard options workflow."
        : "Add a new system option or configuration value.";
    },
  },
};
</script>

<template>
  <app-layout :title="pageTitle">
    <template #header>
      <ActionHeaderLayout
        :title="pageTitle"
        :subtitle="pageSubtitle"
        route-link="system.options.index"
      />
    </template>

    <div class="min-h-screen py-5">
      <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <OptionsForm :data="data" />
      </div>
    </div>
  </app-layout>
</template>