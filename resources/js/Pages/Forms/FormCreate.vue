<script>
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
import FormForm from "./components/FormForm.vue";

export default {
  name: "FormCreate",
  components: {
    FormsHeaderActions,
    FormForm,
  },
};
</script>

<template>
  <AppLayout title="Build New Attendance Form">
    <template #header>
      <forms-header-actions />
    </template>
    <div class="default-container pt-5">
      <FormForm />
    </div>
  </AppLayout>
</template>

<style scoped></style>
