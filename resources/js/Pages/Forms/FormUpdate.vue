<script>
import FormsHeaderActions from "@/Pages/Forms/components/FormsHeaderActions.vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Participant from "@/Modules/domain/Participant";
import FormUpdateDashboard from "@/Pages/Forms/components/FormUpdateDashboard.vue";
import EventCertificates from "@/Pages/Forms/components/EventCertificates.vue";
import FormForm from "./components/FormForm.vue";

export default {
  name: "FormUpdate",
  computed: {
    Participant() {
      return Participant;
    },
    styleTokensError() {
      if (!this.form?.errors) {
        return null;
      }

      const entry = Object.entries(this.form.errors).find(([key]) =>
        key.startsWith("style_tokens")
      );
      return entry ? entry[1] : null;
    },
  },
  components: {
    FormUpdateDashboard,
    EventCertificates,
    FormsHeaderActions,
    FormForm,
  },
  mixins: [ApiMixin],
  data() {
    return {
      activeTab: "dashboard",
    };
  },
};
</script>

<template>
  <AppLayout title="Update Attendance Form">
    <template #header>
      <forms-header-actions />
    </template>
    <div class="default-container">
      <tab-navigation
        v-model="activeTab"
        :tabs="[
          { key: 'dashboard', label: 'Summary' },
          { key: 'update', label: 'Update Form' },
          { key: 'certificates', label: 'eCertificates' },
        ]"
      >
        <template #default="{ activeKey }">
          <div v-if="activeKey === 'update'" class="mt-4 flex justify-center gap-5">
            <form-form :data="data" />
          </div>

          <div v-else-if="activeKey === 'dashboard'" class="mt-4">
            <form-update-dashboard
              :stats="$page.props.eventStats"
              :responses-by-type="$page.props.eventResponsesByType"
              :config="$page.props.data"
            />
          </div>

          <div v-else-if="activeKey === 'certificates'" class="mt-4">
            <event-certificates
              :event-id="data?.event_id"
              :template="$page.props.certificateTemplate"
            />
          </div>
        </template>
      </tab-navigation>
    </div>
  </AppLayout>
</template>

<style scoped></style>
