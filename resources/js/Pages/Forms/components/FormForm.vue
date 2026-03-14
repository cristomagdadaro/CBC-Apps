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
      activeSection: 'details', // 'details', 'style', 'preview'
      isSaving: false,
      showMobilePreview: false,
      windowWidth: typeof window !== 'undefined' ? window.innerWidth : 1024,
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
      if (!this.form?.errors) return null;
      const entry = Object.entries(this.form.errors).find(([key]) =>
        key.startsWith("style_tokens")
      );
      return entry ? entry[1] : null;
    },
    formUrl() {
      return this.data ? route('forms.guest.index', this.form.event_id) : null;
    },
    canPreview() {
      return !!this.form.title && !!this.form.date_from;
    },
    isMobile() {
      return this.windowWidth < 1024;
    },
  },
  methods: {
    async submitProxy() {
      this.isSaving = true;
      this.form.requirements = this.form.requirements || [];
      try {
        if (this.isEdit) {
          await this.submitUpdate();
          this.setRequirements();
        } else {
          await this.submitCreate();
        }
      } finally {
        this.isSaving = false;
      }
    },
    handleResize() {
      if (typeof window === 'undefined') return;
      this.windowWidth = window.innerWidth;
    },
    setRequirements() {
      if (!this.form.requirements) {
        this.form.requirements = this.$page.props?.data?.requirements || [];
      }
    },
    copyFormLink() {
      if (this.formUrl) {
        navigator.clipboard.writeText(this.formUrl);
        // Could add toast notification here
      }
    },
  },
  mounted() {
    if (typeof window !== 'undefined') {
      window.addEventListener('resize', this.handleResize);
    }
  },
  beforeUnmount() {
    if (typeof window !== 'undefined') {
      window.removeEventListener('resize', this.handleResize);
    }
  },
};
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Mobile Navigation -->
    <div class="lg:hidden sticky top-0 z-40 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-3">

      <!-- Mobile Section Tabs -->
      <div class="flex gap-1 mt-3 -mb-3 overflow-x-auto no-scrollbar">
        <button 
          @click="activeSection = 'details'"
          class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
          :class="activeSection === 'details' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 dark:text-gray-400'"
        >
          <LuFileText class="w-4 h-4 inline mr-1" />
          Details
        </button>
        <button 
          @click="activeSection = 'style'"
          class="px-4 py-2 text-sm font-medium border-b-2 transition-colors whitespace-nowrap"
          :class="activeSection === 'style' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-600 dark:text-gray-400'"
        >
          <LuPalette class="w-4 h-4 inline mr-1" />
          Theme
        </button>
        <button 
          @click="showMobilePreview = true"
          class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-gray-600 dark:text-gray-400 whitespace-nowrap"
        >
          <LuEye class="w-4 h-4 inline mr-1" />
          Preview
        </button>
      </div>
    </div>

    <!-- Desktop Header -->
    <div class="hidden lg:block bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
      <div class="mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center gap-4">
            <div>
              <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ isEdit ? 'Edit Form' : 'Create New Form' }}
              </h1>
              <p class="text-xs text-gray-500 dark:text-gray-400">
                {{ form.event_id ? `#${form.event_id}` : 'Draft' }}
              </p>
            </div>
          </div>
          
          <div class="flex items-center gap-3">
            <a 
              v-if="formUrl"
              :href="formUrl"
              target="_blank"
              class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
            >
              <LuExternalLink class="w-4 h-4" />
              Open Form
            </a>
            <button 
              @click="copyFormLink"
              v-if="formUrl"
              class="p-2 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors"
              title="Copy link"
            >
              <LuCopy class="w-4 h-4" />
            </button>
            <div class="h-6 w-px bg-gray-300 dark:bg-gray-600 mx-1" />
            <suspend-form-btn v-if="isEdit" :form="form" />
            <button 
              @click="submitProxy" 
              :disabled="processing || isSaving"
              class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors"
            >
              <LuSave v-if="!processing && !isSaving" class="w-4 h-4" />
              <LuLoader2 v-else class="w-4 h-4 animate-spin" />
              {{ processing || isSaving ? 'Saving...' : 'Save Form' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Sidebar (Desktop) -->
        <div class="hidden lg:block lg:col-span-2 space-y-4">
          <!-- Navigation -->
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <nav class="flex flex-col">
              <button 
                @click="activeSection = 'details'"
                class="flex items-center gap-3 px-4 py-3 text-left transition-colors"
                :class="activeSection === 'details' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
              >
                <LuFileText class="w-5 h-5" />
                <span class="font-medium">Event Details</span>
              </button>
              <button 
                @click="activeSection = 'requirements'"
                class="flex items-center gap-3 px-4 py-3 text-left transition-colors"
                :class="activeSection === 'requirements' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
              >
                <LuListChecks class="w-5 h-5" />
                <span class="font-medium">Attached Forms</span>
              </button>
              <button 
                @click="activeSection = 'style'"
                class="flex items-center gap-3 px-4 py-3 text-left transition-colors"
                :class="activeSection === 'style' ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-r-2 border-blue-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50'"
              >
                <LuPalette class="w-5 h-5" />
                <span class="font-medium">Theme & Style</span>
              </button>
            </nav>
          </div>

          <!-- Quick Stats -->
          <div v-if="isEdit" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 space-y-3">
            <h3 class="font-medium text-gray-900 dark:text-white text-sm">Form Status</h3>
            <div class="flex items-center gap-2">
              <span 
                class="w-2 h-2 rounded-full"
                :class="form.is_suspended ? 'bg-red-500' : form.is_active ? 'bg-green-500' : 'bg-gray-400'"
              />
              <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ form.is_suspended ? 'Suspended' : form.is_active ? 'Active' : 'Draft' }}
              </span>
            </div>
            <div v-if="form.responses_count !== undefined" class="pt-2 border-t border-gray-200 dark:border-gray-700">
              <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ form.responses_count }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Total Responses</p>
            </div>
          </div>
        </div>

        <!-- Main Editor Area -->
        <div class="lg:col-span-5 space-y-6">
          <form v-if="!!form" @submit.prevent="submitProxy" class="space-y-6">
            
            <!-- Event Details Section -->
            <div 
              v-show="activeSection === 'details' || !isMobile"
              class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
            >
              <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                  <LuFileText class="w-5 h-5 text-blue-600" />
                  Event Details
                </h2>
              </div>
              
              <div class="p-4 space-y-4">
                <!-- Title & ID -->
                <div class="flex gap-4">
                  <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Form Title</label>
                    <text-input
                      placeholder="Enter form title"
                      v-model="form.title"
                      :error="form.errors.title"
                      class="w-full"
                    />
                  </div>
                  <div class="w-24 flex-shrink-0">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ID</label>
                    <div class="px-3 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg text-center font-mono text-sm text-gray-600 dark:text-gray-400">
                      {{ form.event_id || '—' }}
                    </div>
                  </div>
                </div>

                <!-- Description -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                  <text-area
                    placeholder="Describe your event or form purpose"
                    v-model="form.description"
                    :error="form.errors.description"
                    class="w-full text-sm"
                    :rows="3"
                  />
                </div>

                <!-- Date & Time Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                        <LuCalendar class="w-4 h-4" />
                        Start Date
                      </label>
                      <date-input
                        v-model="form.date_from"
                        :error="form.errors.date_from"
                        class="w-full"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                        <LuClock class="w-4 h-4" />
                        Start Time
                      </label>
                      <time-input
                        v-model="form.time_from"
                        :error="form.errors.time_from"
                        class="w-full"
                      />
                    </div>
                  </div>
                  
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                        <LuCalendar class="w-4 h-4" />
                        End Date
                      </label>
                      <date-input
                        v-model="form.date_to"
                        :error="form.errors.date_to"
                        class="w-full"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                        <LuClock class="w-4 h-4" />
                        End Time
                      </label>
                      <time-input
                        v-model="form.time_to"
                        :error="form.errors.time_to"
                        class="w-full"
                      />
                    </div>
                  </div>
                </div>

                <!-- Venue -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 flex items-center gap-1">
                    <LuMapPin class="w-4 h-4" />
                    Venue
                  </label>
                  <text-input
                    placeholder="Event location or online link"
                    v-model="form.venue"
                    :error="form.errors.venue"
                    class="w-full"
                  />
                </div>

                <!-- Details -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Additional Details</label>
                  <text-area
                    placeholder="Any other important information for participants"
                    v-model="form.details"
                    :error="form.errors.details"
                    class="w-full text-sm"
                    :rows="4"
                  />
                </div>
              </div>
            </div>

            <!-- Requirements Section -->
            <div 
              v-show="activeSection === 'details' || !isMobile"
              class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
            >
              <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                  <LuListChecks class="w-5 h-5 text-blue-600" />
                  Form Requirements
                </h2>
                <p class="text-sm text-gray-500 mt-1">Attach other forms that must be completed along with this event</p>
              </div>
              <div class="p-4">
                <requirements-manager
                  v-model="form.requirements"
                  :error="form.errors.requirements"
                />
              </div>
            </div>

            <!-- Style Section -->
            <div 
              v-show="activeSection === 'style' || !isMobile"
              class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden"
            >
              <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-700/30">
                <h2 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                  <LuPalette class="w-5 h-5 text-blue-600" />
                  Theme & Appearance
                </h2>
                <p class="text-sm text-gray-500 mt-1">Customize the appearance of your form elements</p>
              </div>
              <div class="p-4">
                <form-style-designer
                  v-model="form.style_tokens"
                  :error="styleTokensError"
                />
              </div>
            </div>

            <!-- Mobile Save Button -->
            <div class="lg:hidden pt-4">
              <button 
                @click="submitProxy" 
                :disabled="processing || isSaving"
                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-medium rounded-xl transition-colors"
              >
                <LuSave v-if="!processing && !isSaving" class="w-5 h-5" />
                <LuLoader2 v-else class="w-5 h-5 animate-spin" />
                {{ processing || isSaving ? 'Saving Form...' : 'Save Form' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Preview Panel (Desktop) -->
        <div class="hidden lg:block lg:col-span-5">
          <div class="sticky top-24 space-y-4">
            <div class="flex items-center justify-between">
              <h3 class="font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <LuEye class="w-5 h-5" />
                Live Preview
              </h3>
              <span v-if="!canPreview" class="text-xs text-amber-600 bg-amber-50 dark:bg-amber-900/20 px-2 py-1 rounded-full">
                Add title & date to preview
              </span>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden max-h-[calc(100vh-200px)] overflow-y-auto">
                <guest-card 
                  v-if="canPreview" 
                  :data="form" 
                  class="w-full"
                />
                <div v-else class="p-8 text-center text-gray-400 dark:text-gray-500">
                  <LuFileQuestion class="w-12 h-12 mx-auto mb-3 opacity-50" />
                  <p>Fill in the form details to see preview</p>
                </div>
              </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Preview Modal -->
    <Teleport to="body">
      <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div 
          v-if="showMobilePreview"
          class="fixed inset-0 z-50 bg-black/50 lg:hidden"
          @click="showMobilePreview = false"
        >
          <div 
            class="absolute inset-x-0 bottom-0 top-16 bg-gray-100 dark:bg-gray-900 overflow-y-auto"
            @click.stop
          >
            <div class="p-4">
              <guest-card 
                v-if="canPreview" 
                :data="form" 
                class="w-full"
              />
              <div v-else class="bg-white dark:bg-gray-800 rounded-xl p-8 text-center text-gray-400 dark:text-gray-500">
                <LuFileQuestion class="w-12 h-12 mx-auto mb-3 opacity-50" />
                <p>Add title and date to preview</p>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<style scoped>
/* Hide scrollbar */
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

/* Smooth transitions */
.transition-colors {
  transition-property: background-color, border-color, color, fill, stroke;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>