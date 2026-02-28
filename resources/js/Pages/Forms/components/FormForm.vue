<script>
import ListOfForms from "@/Pages/Forms/components/ListOfForms.vue";
import Form from "@/Modules/domain/Form";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import RequirementsManager from "@/Components/Forms/RequirementsManager.vue";
import FormStyleDesigner from "@/Pages/Forms/components/FormStyleDesigner.vue";
import SuspendFormBtn from "./SuspendFormBtn.vue";

export default {
  name: "FormCreate",
  components: {
    FormStyleDesigner,
    RequirementsManager,
    ListOfForms,
    SuspendFormBtn,
  },
  mixins: [ApiMixin],
  data() {
    return {
      isEdit: !!this.data,
    };
  },
  beforeMount() {
    this.model = new Form();
    if (this.data) {
      this.setFormAction("update");
      this.setRequirements();
    } else {
      this.setFormAction("create");
      if (!this.form.requirements) {
        this.form.requirements = [];
      }
    }
  },
  computed: {
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
  methods: {
    async submitProxy() {
      this.form.requirements = this.form.requirements || [];
      if (this.isEdit) {
        await this.submitUpdate();
        this.setRequirements();
      } else {
        await this.submitCreate();
      }
    },
    setRequirements() {
      if (!this.form.requirements) {
        this.form.requirements = this.$page.props?.data?.requirements || [];
      }
    },
  },
};
</script>
<template>
  <div class="grid grid-cols-1 md:grid-cols-3 justify-center gap-5">
    <div class="flex flex-col">
      <span class="font-semibold text-gray-700">Event Details</span>
      <form v-if="!!form" @submit.prevent="submitProxy">
        <div class="w-full mx-auto">
          <div class="bg-white dark:bg-gray-800 overflow-hidden sm:rounded-lg">
            <div class="border p-2 rounded-md flex flex-col gap-2">
              <div
                class="flex flex-row w-full gap-2 bg-gray-200 p-2 rounded-md justify-between shadow py-4"
              >
                <div class="flex flex-col justify-center gap-1 w-full">
                  <text-input
                    placeholder="Title"
                    v-model="form.title"
                    :error="form.errors.title"
                  />
                  <text-area
                    placeholder="Form Description"
                    v-model="form.description"
                    :error="form.errors.description"
                    class="text-xs"
                  />
                </div>
                <div class="flex flex-col items-center justify-center">
                  <label class="text-2xl leading-none font-[1000]">{{
                    form.event_id ?? "####"
                  }}</label>
                  <span class="text-[0.6rem] leading-none">Form ID</span>
                </div>
              </div>
              <div class="grid grid-cols-2 grid-rows-2 px-1 gap-2">
                <div>
                  <span class="font-bold uppercase">Start Date: </span>
                  <date-input
                    v-model="form.date_from"
                    :error="form.errors.date_from"
                  />
                </div>
                <div>
                  <span class="font-bold uppercase">End Date: </span>
                  <date-input
                    v-model="form.date_to"
                    :error="form.errors.date_to"
                  />
                </div>
                <div>
                  <span class="font-bold uppercase">Start Time: </span>
                  <time-input
                    v-model="form.time_from"
                    :error="form.errors.time_from"
                  />
                </div>
                <div>
                  <span class="font-bold uppercase">End Time: </span>
                  <time-input
                    v-model="form.time_to"
                    :error="form.errors.time_to"
                  />
                </div>
              </div>
              <div class="px-1 flex flex-col gap-1">
                <div>
                  <span class="font-bold uppercase">Venue: </span>
                  <text-input
                    placeholder="Venue"
                    v-model="form.venue"
                    class="text-sm"
                    :error="form.errors.venue"
                  />
                </div>
                <div>
                  <span class="font-bold uppercase">Other details: </span>
                  <text-area
                    placeholder="Other details"
                    v-model="form.details"
                    class="w-full text-xs"
                    :error="form.errors.details"
                  />
                </div>
              </div><pre>{{ form.requirements }}</pre>
              <div class="px-1 flex flex-col gap-1">
                <requirements-manager
                  v-model="form.requirements"
                  :error="form.errors.requirements"
                />
              </div>
              <div class="flex flex-col p-2">
                <div class="flex gap-1 justify-between">
                  <suspend-form-btn v-if="isEdit" :form="form" />
                  <button
                    class="bg-blue-200 text-blue-900 w-fit px-4 py-2 rounded disabled:opacity-50"
                    :disabled="processing"
                    title="Temporarily stop accepting responses"
                  >
                    <span v-if="processing">Saving...</span>
                    <span v-else>Save</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="px-1 flex flex-col gap-1">
      <form-style-designer
        v-model="form.style_tokens"
        :error="styleTokensError"
      />
    </div>
    <div class="flex flex-col w-fit">
      <div class="flex justify-between text-sm">
        <span class="font-semibold text-gray-700">Preview</span>
        <a
          v-if="data"
          class="font-semibold text-gray-700 mr-1"
          title="Visit Form"
          :href="route('forms.guest.index', form.event_id)"
          target="_blank"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="currentColor"
            class="w-auto h-5"
            viewBox="0 0 16 16"
          >
            <path
              fill-rule="evenodd"
              d="M6.364 13.5a.5.5 0 0 0 .5.5H13.5a1.5 1.5 0 0 0 1.5-1.5v-10A1.5 1.5 0 0 0 13.5 1h-10A1.5 1.5 0 0 0 2 2.5v6.636a.5.5 0 1 0 1 0V2.5a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v10a.5.5 0 0 1-.5.5H6.864a.5.5 0 0 0-.5.5"
            />
            <path
              fill-rule="evenodd"
              d="M11 5.5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793l-8.147 8.146a.5.5 0 0 0 .708.708L10 6.707V10.5a.5.5 0 0 0 1 0z"
            />
          </svg>
        </a>
      </div>
      <guest-card :data="form" class="bg-white drop-shadow-lg w-full" />
    </div>
  </div>
</template>
