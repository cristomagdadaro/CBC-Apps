<script>
import ConcreteApiService from "@/Modules/infrastructure/ConcreteApiService";
import ApiMixin from "@/Modules/mixins/ApiMixin";

export default {
    emits: ["update:modelValue"],
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
        autocomplete: {
            type: String,
            default: "off",
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
        },
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
            currentSearch: "",
            debounceTimeout: null,
            selectedOption: null,
            isUsingLocalOptions: false,
            dropdownMaxHeight: 300,
            dropdownWidth: null,
            openDropdownUpwards: false,
        };
    },
    methods: {
        toggleDropdown() {
            if (this.disabled) return;

            if (!this.showDropdown) {
                this.showDropdown = true;
                this.autoFocus = true;
                this.$nextTick(() => this.updateDropdownSizing());

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

                this.fetchedResponse = await this.api.getApi(
                    this.apiLink,
                    params,
                );

                // Normalize possible payload shapes
                let items = [];
                if (Array.isArray(this.fetchedResponse?.data?.data))
                    items = this.fetchedResponse.data.data;
                else if (Array.isArray(this.fetchedResponse?.data))
                    items = this.fetchedResponse.data;
                else if (Array.isArray(this.fetchedResponse))
                    items = this.fetchedResponse;

                if (items.length) {
                    const newOptions = items
                        .map((item) => this.formatOption(item))
                        .filter(Boolean);

                    this.formattedOptions = append
                        ? [...this.formattedOptions, ...newOptions]
                        : newOptions;

                    this.filteredOptions = this.formattedOptions;
                    this.hasMoreData = newOptions.length === this.perPage;
                } else if (!append) {
                    this.filteredOptions = [];
                    this.hasMoreData = false;
                }

                this.$nextTick(() => this.updateDropdownSizing());
            } catch (error) {
                console.error("Error fetching options:", error);
            } finally {
                this.isLoadingMore = false;
            }
        },

        async loadMoreOptions() {
            if (!this.hasMoreData || this.isLoadingMore || !this.api) return;

            this.currentPage++;
            await this.getOptionsFromApi(
                this.currentSearch,
                this.currentPage,
                true,
            );
        },

        selectOption(option) {
            this.selectedOption = option;
            this.$emit("update:modelValue", option.value);
            this.displayedInput = option.label;
            // Clear any pending search debounce to avoid late fetches
            if (this.debounceTimeout) {
                clearTimeout(this.debounceTimeout);
                this.debounceTimeout = null;
            }
            this.currentSearch = "";
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
                const value =
                    typeof eventOrValue === "string"
                        ? eventOrValue
                        : (eventOrValue?.target?.value ?? "");
                this.handleSearch(value);
            }, 300);
        },

        handleSearch(searchValue) {
            this.currentSearch = searchValue;

            // If we have local options, search offline without resetting loaded local data.
            if (this.options && this.options.length > 0) {
                if (!this.isUsingLocalOptions || !this.formattedOptions.length) {
                    this.initLocalOptions();
                }
                this.searchLocalOptions(searchValue);
            } else if (this.apiLink) {
                // Otherwise search via API and reset pagination for remote results.
                this.resetPagination();
                this.getOptionsFromApi(searchValue);
            }
        },

        normalizeForSearch(text) {
            return String(text ?? "")
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .replace(/[^a-z0-9\s-]/g, " ")
                .replace(/\s+/g, " ")
                .trim();
        },

        searchLocalOptions(searchValue) {
            if (!searchValue) {
                // If no search, show all options
                this.filteredOptions = [...this.formattedOptions];
            } else {
                const normalizedSearch = this.normalizeForSearch(searchValue);
                const searchTerms = normalizedSearch
                    .split(/\s+/)
                    .filter((t) => t.length > 0); // Split into individual terms, filter empty

                // Score and filter options
                const scored = this.formattedOptions
                    .map((option) => {
                        // Normalize label and value so search handles case, accents, and punctuation.
                        const label = this.normalizeForSearch(option.label);
                        const value = this.normalizeForSearch(option.value);
                        const combinedText = `${label} ${value}`;

                        let score = 0;

                        // Exact match on full label (highest priority)
                        if (label === normalizedSearch) {
                            score += 1000;
                        }
                        // Exact match on full value
                        else if (value === normalizedSearch) {
                            score += 900;
                        }
                        // Label starts with search term (prefix match)
                        else if (label.startsWith(normalizedSearch)) {
                            score += 500;
                        }
                        // All search terms appear in label (word match)
                        else if (
                            searchTerms.length > 0 &&
                            searchTerms.every((term) => label.includes(term))
                        ) {
                            score += 400;
                        }
                        // Any single term appears in label
                        else if (
                            searchTerms.length > 0 &&
                            searchTerms.some((term) => label.includes(term))
                        ) {
                            score += 250;
                        }
                        // All search terms appear in combined text
                        else if (
                            searchTerms.length > 0 &&
                            searchTerms.every((term) =>
                                combinedText.includes(term),
                            )
                        ) {
                            score += 300;
                        }
                        // Any single term appears in combined text
                        else if (
                            searchTerms.length > 0 &&
                            searchTerms.some((term) => combinedText.includes(term))
                        ) {
                            score += 150;
                        }
                        // Search term appears anywhere in label
                        else if (label.includes(normalizedSearch)) {
                            score += 200;
                        }
                        // Search term appears anywhere in combined text
                        else if (combinedText.includes(normalizedSearch)) {
                            score += 100;
                        }

                        return { option, score };
                    })
                    .filter(({ score }) => score > 0)
                    .sort((a, b) => b.score - a.score)
                    .map(({ option }) => option);

                this.filteredOptions = scored;
            }

            this.$nextTick(() => this.updateDropdownSizing());
        },

        updateDropdownSizing() {
            if (!this.$el) return;

            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            const hostRect = this.$el.getBoundingClientRect();
            const safetyMargin = 16;

            const spaceBelow = viewportHeight - hostRect.bottom - safetyMargin;
            const spaceAbove = hostRect.top - safetyMargin;

            this.openDropdownUpwards = spaceBelow < 220 && spaceAbove > spaceBelow;

            const availableHeight = this.openDropdownUpwards ? spaceAbove : spaceBelow;
            //this.dropdownMaxHeight = Math.max(140, Math.floor(availableHeight));

            const labels = (this.filteredOptions?.length
                ? this.filteredOptions
                : this.formattedOptions
            ).map((option) => String(option?.label ?? ""));

            const longestLabelLength = labels.reduce(
                (max, label) => Math.max(max, label.length),
                0,
            );

            // Approximate width from character count and keep it inside viewport.
            const contentWidth = Math.max(220, Math.floor(longestLabelLength * 8.2 + 56));
            const triggerWidth = Math.floor(hostRect.width || 220);
            const maxViewportWidth = Math.max(220, viewportWidth - safetyMargin * 2);
            this.dropdownWidth = Math.min(
                Math.max(triggerWidth, contentWidth),
                maxViewportWidth,
            );
        },

        handleWindowResize() {
            if (this.showDropdown) {
                this.updateDropdownSizing();
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
            this.$emit("update:modelValue", null);
        },

        // Load selected option data on mount if modelValue exists
        async loadSelectedOption() {
            const hasValue =
                this.modelValue !== null &&
                this.modelValue !== undefined &&
                this.modelValue !== "";
            if (!hasValue) return;

            if (this.api) {
                try {
                    const response = await this.api.getApi(this.apiLink, {
                        filter: "id",
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
                    console.error("Error loading selected option:", error);
                }
            } else {
                // Fallback to options provided in props
                if (!this.formattedOptions.length) this.initLocalOptions();
                const found = this.formattedOptions.find(
                    (o) => String(o.value) === String(this.modelValue),
                );
                if (found) {
                    this.selectedOption = found;
                    this.displayedInput = found.label;
                }
            }

            // If nothing matched, show the raw modelValue as initial input
            if (
                !this.selectedOption &&
                (this.displayedInput == null || this.displayedInput === "")
            ) {
                this.displayedInput = String(this.modelValue);
            }
        },

        // Add back missing helpers used by loadSelectedOption
        formatOption(option) {
            if (option == null) return null;
            if (typeof option === "string" || typeof option === "number") {
                return { value: option, label: String(option) };
            }
            const fullName = option.full_name
                ? option.full_name
                : option.first_name || option.last_name
                  ? `${option.first_name || ""} ${option.last_name || ""}`.trim()
                  : null;
            return {
                value: option.id || option.value,
                label:
                    option.name ||
                    option.title ||
                    option.label ||
                    option.value ||
                    fullName ||
                    String(option.id ?? option.value ?? ""),
            };
        },
        initLocalOptions() {
            const mapped = (this.options || [])
                .map(this.formatOption)
                .filter(Boolean);
            this.formattedOptions = mapped;
            this.filteredOptions = mapped;
            // Mark that we're using local options so we know not to fetch from API
            this.isUsingLocalOptions = true;
            this.$nextTick(() => this.updateDropdownSizing());
        },
    },

    expose: ["focus"],

    async mounted() {
        if (this.apiLink) {
            this.api = new ConcreteApiService();
        }
        // Always attempt to load initial selection from modelValue
        await this.loadSelectedOption();

        document.addEventListener("click", this.handleClickOutside);
        window.addEventListener("resize", this.handleWindowResize);
    },

    beforeUnmount() {
        document.removeEventListener("click", this.handleClickOutside);
        window.removeEventListener("resize", this.handleWindowResize);
        if (this.debounceTimeout) {
            clearTimeout(this.debounceTimeout);
        }
    },

    watch: {
        async modelValue(newVal, oldVal) {
            if (newVal !== oldVal) {
                if (newVal !== null && newVal !== undefined && newVal !== "") {
                    // If selection already matches, skip reloading to avoid flicker/reverts
                    if (
                        this.selectedOption &&
                        String(this.selectedOption.value) === String(newVal)
                    ) {
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

        filteredOptions() {
            if (this.showDropdown) {
                this.$nextTick(() => this.updateDropdownSizing());
            }
        },

        "api.processing"(newVal) {
            if (!newVal && this.autoFocus) {
                this.$nextTick(() => {
                    this.$refs?.textInput?.focus();
                });
            }
        },
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
                return "Loading options...";
            }
            if (this.currentSearch && !this.hasOptions) {
                return "No results found";
            }
            if (!this.currentSearch && !this.hasOptions) {
                return "Type to search...";
            }
            return "";
        },

        dropdownStyle() {
            return {
                top: this.openDropdownUpwards ? "auto" : "100%",
                bottom: this.openDropdownUpwards ? "100%" : "auto",
                marginTop: this.openDropdownUpwards ? "0" : "0.5rem",
                marginBottom: this.openDropdownUpwards ? "0.5rem" : "0",
                width: this.dropdownWidth ? `${this.dropdownWidth}px` : "100%",
                maxWidth: "calc(100vw - 2rem)",
            };
        },
    },
};
</script>

<template>
    <div class="relative flex flex-col w-full p-0 bg-transparent border-0">
        <div class="flex flex-col w-full">
            <div class="flex items-center gap-1">
                <text-input
                    :id="id"
                    :name="id"
                    ref="textInput"
                    :title="title"
                    :label="dynamicLabel"
                    :error="$attrs.error"
                    :required="required"
                    :show-clear="!disabled"
                    :disabled="disabled"
                    v-model="displayedInput"
                    :placeholder="placeholder"
                    :autocomplete="autocomplete"
                    @focusin="toggleDropdown()"
                    @click="toggleDropdown()"
                    @input="debounceApiCall($event)"
                    @clear="clearSelection"
                    ><button v-if="!disabled" class="p-2 m-1 text-white rounded-md bg-AB">
                        <search-icon
                            class="w-5 h-5 pointer-events-none"
                        /></button
                ></text-input>
            </div>

            <transition-container>
                <div
                    v-show="showDropdown"
                    class="absolute left-0 border border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-lg bg-white z-[999] min-w-full"
                    :style="dropdownStyle"
                >
                    <!-- Dropdown Header -->
                    <div
                        v-if="!processing || hasOptions"
                        class="px-3 py-2 text-xs text-gray-500 border-b border-gray-100 bg-gray-50 rounded-t-md"
                    >
                        <p v-if="hasOptions">
                            {{ filteredOptions.length }} option{{
                                filteredOptions.length !== 1 ? "s" : ""
                            }}
                            <span
                                v-if="hasMoreData && !options.length"
                                class="text-gray-400"
                                >(scroll for more)</span
                            >
                        </p>
                        <p v-else>{{ emptyStateMessage }}</p>
                    </div>

                    <!-- Options List -->
                    <div
                        v-if="hasOptions"
                        class="overflow-y-auto"
                        :style="{ maxHeight: `${dropdownMaxHeight}px` }"
                        @scroll="handleDropdownScroll"
                    >
                        <div
                            v-for="option in filteredOptions"
                            :key="option.value"
                            @click="selectOption(option)"
                            class="px-3 py-2 transition-colors duration-150 border-b cursor-pointer hover:bg-indigo-50 border-gray-50 last:border-b-0"
                            :class="{
                                'bg-indigo-100 text-indigo-900':
                                    selectedOption?.value === option.value,
                                'text-gray-900':
                                    selectedOption?.value !== option.value,
                            }"
                        >
                            <div
                                class="overflow-hidden text-ellipsis whitespace-nowrap"
                                :title="option.label"
                            >
                                {{ option.label }}
                            </div>
                        </div>

                        <!-- Loading More Indicator -->
                        <div
                            v-if="showLoadingMore"
                            class="px-3 py-2 text-sm text-center text-gray-500 border-t border-gray-100"
                        >
                            Loading more options...
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-else-if="!processing"
                        class="px-3 py-4 text-sm text-center text-gray-500"
                    >
                        {{ emptyStateMessage }}
                    </div>

                    <!-- Initial Loading State -->
                    <div
                        v-else
                        class="px-3 py-4 text-sm text-center text-gray-500"
                    >
                        <div class="flex items-center justify-center space-x-2">
                            <div
                                class="w-4 h-4 border-b-2 border-indigo-500 rounded-full animate-spin"
                            ></div>
                            <span>Loading options...</span>
                        </div>
                    </div>
                </div>
            </transition-container>
        </div>
    </div>
</template>
