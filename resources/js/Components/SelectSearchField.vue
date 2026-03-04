<script>
import ConcreteApiService from "@/Modules/infrastructure/ConcreteApiService";
import ApiMixin from "@/Modules/mixins/ApiMixin";

export default {
    emits: ['update:modelValue'],
    mixins: [ApiMixin],
    props: {
        modelValue: {
            type: [String, Number],
            default: null,
        },
        id: String,
        label: String,
        apiLink: String,
        options: {
            type: Array,
            required: false,
            default: () => [],
        },
        required: {
            type: Boolean,
            default: false,
        },
        placeholder: {
            type: String,
            default: null,
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        title: {
            type: String,
            default: null,
        },
        perPage: {
            type: Number,
            default: 10,
        }
    },
    data() {
        return {
            api: null,
            fetchedResponse: null,
            formattedOptions: [],
            filteredOptions: [],
            showDropdown: false,
            displayedInput: null,
            autoFocus: false,
            currentPage: 1,
            hasMoreData: true,
            isLoadingMore: false,
            currentSearch: '',
            debounceTimeout: null,
            selectedOption: null,
            isUsingLocalOptions: false,
        };
    },
    methods: {
        toggleDropdown() {
            if (this.disabled) return;

            if (!this.showDropdown) {
                this.showDropdown = true;
                this.autoFocus = true;

                // Load initial data if not already loaded
                if (this.formattedOptions.length === 0) {
                    this.resetPagination();
                    // Check if we have local options first
                    if (this.options && this.options.length > 0) {
                        this.initLocalOptions();
                    } else if (this.apiLink) {
                        // Fall back to API if no local options
                        this.getOptionsFromApi();
                    }
                }
            } else {
                this.closeDropdown();
            }
        },

        async getOptionsFromApi(search = null, page = 1, append = false) {
            if (!this.api || !this.apiLink) return;

            try {
                // Prevent multiple simultaneous requests
                if (this.isLoadingMore && append) return;

                this.isLoadingMore = append;

                const params = {
                    per_page: this.perPage,
                    page,
                    ...(search ? { search } : {}),
                };

                this.fetchedResponse = await this.api.getApi(this.apiLink, params);

                // Normalize possible payload shapes
                let items = [];
                if (Array.isArray(this.fetchedResponse?.data?.data)) items = this.fetchedResponse.data.data;
                else if (Array.isArray(this.fetchedResponse?.data)) items = this.fetchedResponse.data;
                else if (Array.isArray(this.fetchedResponse)) items = this.fetchedResponse;

                if (items.length) {
                    const newOptions = items.map(item => this.formatOption(item)).filter(Boolean);

                    this.formattedOptions = append
                        ? [...this.formattedOptions, ...newOptions]
                        : newOptions;

                    this.filteredOptions = this.formattedOptions;
                    this.hasMoreData = newOptions.length === this.perPage;
                }
            } catch (error) {
                console.error('Error fetching options:', error);
            } finally {
                this.isLoadingMore = false;
            }
        },

        async loadMoreOptions() {
            if (!this.hasMoreData || this.isLoadingMore || !this.api) return;

            this.currentPage++;
            await this.getOptionsFromApi(this.currentSearch, this.currentPage, true);
        },

        selectOption(option) {
            this.selectedOption = option;
            this.$emit('update:modelValue', option.value);
            this.displayedInput = option.label;
            // Clear any pending search debounce to avoid late fetches
            if (this.debounceTimeout) {
                clearTimeout(this.debounceTimeout);
                this.debounceTimeout = null;
            }
            this.currentSearch = '';
            this.closeDropdown();
        },

        closeDropdown() {
            this.showDropdown = false;
            this.autoFocus = false;
        },

        resetPagination() {
            this.currentPage = 1;
            this.hasMoreData = true;
            this.formattedOptions = [];
            this.filteredOptions = [];
        },

        debounceApiCall(eventOrValue) {
            clearTimeout(this.debounceTimeout);
            this.debounceTimeout = setTimeout(() => {
                const value = typeof eventOrValue === 'string'
                    ? eventOrValue
                    : eventOrValue?.target?.value ?? '';
                this.handleSearch(value);
            }, 300);
        },

        handleSearch(searchValue) {
            this.currentSearch = searchValue;
            this.resetPagination();
            
            // If we have local options (from props) OR fetched options in local mode, search offline
            if ((this.options && this.options.length > 0) || (this.isUsingLocalOptions && this.formattedOptions.length > 0)) {
                this.searchLocalOptions(searchValue);
            } else if (this.apiLink) {
                // Otherwise search via API
                this.getOptionsFromApi(searchValue);
            }
        },

        searchLocalOptions(searchValue) {
            if (!searchValue) {
                // If no search, show all options
                this.filteredOptions = [...this.formattedOptions];
            } else {
                const searchLower = searchValue.toLowerCase().trim();
                const searchTerms = searchLower.split(/\s+/).filter(t => t.length > 0); // Split into individual terms, filter empty

                // Score and filter options
                const scored = this.formattedOptions
                    .map(option => {
                        // Trim and normalize both label and value
                        const label = String(option.label).toLowerCase().trim();
                        const value = String(option.value).toLowerCase().trim();
                        const combinedText = `${label} ${value}`;

                        let score = 0;

                        // Exact match on full label (highest priority)
                        if (label === searchLower) {
                            score += 1000;
                        }
                        // Exact match on full value
                        else if (value === searchLower) {
                            score += 900;
                        }
                        // Label starts with search term (prefix match)
                        else if (label.startsWith(searchLower)) {
                            score += 500;
                        }
                        // All search terms appear in label (word match)
                        else if (searchTerms.length > 0 && searchTerms.every(term => label.includes(term))) {
                            score += 400;
                        }
                        // All search terms appear in combined text
                        else if (searchTerms.length > 0 && searchTerms.every(term => combinedText.includes(term))) {
                            score += 300;
                        }
                        // Search term appears anywhere in label
                        else if (label.includes(searchLower)) {
                            score += 200;
                        }
                        // Search term appears anywhere in combined text
                        else if (combinedText.includes(searchLower)) {
                            score += 100;
                        }

                        return { option, score };
                    })
                    .filter(({ score }) => score > 0)
                    .sort((a, b) => b.score - a.score)
                    .map(({ option }) => option);

                this.filteredOptions = scored;
            }
        },

        handleDropdownScroll(event) {
            const { scrollTop, scrollHeight, clientHeight } = event.target;
            const threshold = 50; // pixels from bottom

            if (scrollHeight - scrollTop - clientHeight < threshold) {
                this.loadMoreOptions();
            }
        },

        handleClickOutside(event) {
            if (this.$el && !this.$el.contains(event.target)) {
                this.closeDropdown();
            }
        },

        clearSelection() {
            this.selectedOption = null;
            this.displayedInput = null;
            this.$emit('update:modelValue', null);
        },

        // Load selected option data on mount if modelValue exists
        async loadSelectedOption() {
            const hasValue = this.modelValue !== null && this.modelValue !== undefined && this.modelValue !== '';
            if (!hasValue) return;


            if (this.api) {
                try {
                    const response = await this.api.getApi(this.apiLink, {
                        filter: 'id',
                        search: this.modelValue ?? null,
                        per_page: 1,
                    });

                    const payload = Array.isArray(response?.data?.data)
                        ? response.data.data
                        : Array.isArray(response?.data)
                            ? response.data
                            : Array.isArray(response)
                                ? response
                                : [];

                    if (payload.length) {
                        const opt = this.formatOption(payload[0]);
                        if (opt) {
                            this.selectedOption = opt;
                            this.displayedInput = opt.label;
                        }
                    }
                } catch (error) {
                    console.error('Error loading selected option:', error);
                }
            } else {
                // Fallback to options provided in props
                if (!this.formattedOptions.length) this.initLocalOptions();
                const found = this.formattedOptions.find(o => String(o.value) === String(this.modelValue));
                if (found) {
                    this.selectedOption = found;
                    this.displayedInput = found.label;
                }
            }

            // If nothing matched, show the raw modelValue as initial input
            if (!this.selectedOption && (this.displayedInput == null || this.displayedInput === '')) {
                this.displayedInput = String(this.modelValue);
            }
        },

        // Add back missing helpers used by loadSelectedOption
        formatOption(option) {
            if (option == null) return null;
            if (typeof option === 'string' || typeof option === 'number') {
                return { value: option, label: String(option) };
            }
            const fullName = (option.full_name) ? option.full_name : ((option.first_name || option.last_name) ? `${option.first_name || ''} ${option.last_name || ''}`.trim() : null);
            return {
                value: option.id || option.value,
                label: option.name || option.title || option.label || option.value || fullName || String(option.id ?? option.value ?? ''),
            };
        },
        initLocalOptions() {
            const mapped = (this.options || []).map(this.formatOption).filter(Boolean);
            this.formattedOptions = mapped;
            this.filteredOptions = mapped;
            // Mark that we're using local options so we know not to fetch from API
            this.isUsingLocalOptions = true;
        },
    },

    expose: ['focus'],

    async mounted() {
        if (this.apiLink) {
            this.api = new ConcreteApiService();
        }
        // Always attempt to load initial selection from modelValue
        await this.loadSelectedOption();

        document.addEventListener("click", this.handleClickOutside);
    },

    beforeUnmount() {
        document.removeEventListener("click", this.handleClickOutside);
        if (this.debounceTimeout) {
            clearTimeout(this.debounceTimeout);
        }
    },

    watch: {
        async modelValue(newVal, oldVal) {
            if (newVal !== oldVal) {
                if (newVal !== null && newVal !== undefined && newVal !== '') {
                    // If selection already matches, skip reloading to avoid flicker/reverts
                    if (this.selectedOption && String(this.selectedOption.value) === String(newVal)) {
                        return;
                    }
                    await this.loadSelectedOption();
                } else {
                    this.clearSelection();
                }
            }
        },

        displayedInput(newVal) {
            if (!newVal && this.selectedOption) {
                this.clearSelection();
            }
        },

        'api.processing'(newVal) {
            if (!newVal && this.autoFocus) {
                this.$nextTick(() => {
                    this.$refs?.textInput?.focus();
                });
            }
        }
    },

    computed: {
        dynamicLabel() {
            if (this.processing && !this.isLoadingMore) {
                return `${this.label} (loading)`;
            }
            return this.label;
        },

        hasOptions() {
            return this.filteredOptions && this.filteredOptions.length > 0;
        },

        showLoadingMore() {
            return this.isLoadingMore && this.hasOptions;
        },

        emptyStateMessage() {
            if (this.processing && !this.hasOptions) {
                return 'Loading options...';
            }
            if (this.currentSearch && !this.hasOptions) {
                return 'No results found';
            }
            if (!this.currentSearch && !this.hasOptions) {
                return 'Type to search...';
            }
            return '';
        }
    }
};
</script>

<template>
    <div class="relative flex flex-col border-0 p-0 bg-transparent w-full">
        <div class="flex flex-col w-full">
            <div class="flex items-center gap-1">
                <text-input
                    :id="id"
                    ref="textInput"
                    :title="title"
                    :label="dynamicLabel"
                    :error="$attrs.error"
                    :required="required"
                    :show-clear="!disabled"
                    :disabled="disabled"
                    v-model="displayedInput"
                    :placeholder="placeholder"
                    @focusin="toggleDropdown()"
                    @click="toggleDropdown()"
                    @input="debounceApiCall($event)"
                    @clear="clearSelection"
                ><button class="bg-AB text-white m-1 rounded-md p-2"><search-icon class="h-5 w-5 pointer-events-none" /></button></text-input>
                
            </div>

            <transition-container>
                <div
                    v-show="showDropdown"
                    class="absolute left-0 mt-2 border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-lg bg-white z-[999] min-w-full max-w-[20rem]"
                    style="top: 100%;"
                >
                    <!-- Dropdown Header -->
                    <div
                        v-if="!processing || hasOptions"
                        class="text-xs text-gray-500 px-3 py-2 border-b border-gray-100 bg-gray-50 rounded-t-md"
                    >
                        <p v-if="hasOptions">
                            {{ filteredOptions.length }} option{{ filteredOptions.length !== 1 ? 's' : '' }}
                            <span v-if="hasMoreData && !options.length" class="text-gray-400">(scroll for more)</span>
                        </p>
                        <p v-else>{{ emptyStateMessage }}</p>
                    </div>

                    <!-- Options List -->
                    <div
                        v-if="hasOptions"
                        class="max-h-48 overflow-y-auto"
                        @scroll="handleDropdownScroll"
                    >
                        <div
                            v-for="option in filteredOptions"
                            :key="option.value"
                            @click="selectOption(option)"
                            class="px-3 py-2 hover:bg-indigo-50 cursor-pointer border-b border-gray-50 last:border-b-0 transition-colors duration-150"
                            :class="{
                                'bg-indigo-100 text-indigo-900': selectedOption?.value === option.value,
                                'text-gray-900': selectedOption?.value !== option.value
                            }"
                        >
                            <div class="truncate" :title="option.label">
                                {{ option.label }}
                            </div>
                        </div>

                        <!-- Loading More Indicator -->
                        <div
                            v-if="showLoadingMore"
                            class="px-3 py-2 text-center text-sm text-gray-500 border-t border-gray-100"
                        >
                            Loading more options...
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-else-if="!processing"
                        class="px-3 py-4 text-center text-gray-500 text-sm"
                    >
                        {{ emptyStateMessage }}
                    </div>

                    <!-- Initial Loading State -->
                    <div
                        v-else
                        class="px-3 py-4 text-center text-gray-500 text-sm"
                    >
                        <div class="flex items-center justify-center space-x-2">
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-indigo-500"></div>
                            <span>Loading options...</span>
                        </div>
                    </div>
                </div>
            </transition-container>
        </div>
    </div>
</template>
