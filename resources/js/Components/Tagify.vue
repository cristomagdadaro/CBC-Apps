<script>
import Tagify from '@yaireo/tagify';
import '@yaireo/tagify/dist/tagify.css';
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

export default {
    name: "TagifyInput",
    components: {DropdownLink, Dropdown},
    mixins: [ApiMixin],
    emits: ['update:modelValue'],
    props: {
        modelValue: {
            type: [Array, String],
            default: () => []
        },
        placeholder: String,
        classes: String,
        name: String,
        apiLink: String,
        whitelist: {
            type: Array,
            default: () => [],
        },
        enforceWhitelist: {
            type: Boolean,
            default: false,
        },
    },
    async mounted() {
        await this.initializeTagify();
    },
    watch: {
        modelValue(newVal) {
            this.syncModelValue(newVal);
        },
        whitelist(newVal) {
            this.applyWhitelist(this.mergeData(newVal || [], this.apiData));
        }
    },
    data() {
        return {
            apiData: [],
            filteredData: [],
            searchQuery: '',
            tagify: null,
        }
    },
    methods: {
        async initializeTagify() {
            const fetched = await this.fetchData();
            this.apiData = this.mergeData(this.whitelist || [], fetched);

            this.tagify = new Tagify(this.$refs.input, {
                whitelist: this.apiData,
                enforceWhitelist: this.enforceWhitelist,
                dropdown: {
                    enabled: 0,
                    closeOnSelect: false,
                },
            });

            this.syncModelValue(this.modelValue);

            this.tagify.on('change', this.onTagifyChange);
            this.tagify.on('input', this.onTagifyInput);
        },
        async fetchData() {
            if (!this.apiLink)
                return [];
            
            const params = {
                filter: 'name',
                per_page: '*',
            }
            try {
                const response = await this.fetchGetApi(this.apiLink, params); console.log(response);
                const payload = response?.data ?? response;
                const list = Array.isArray(payload)
                    ? payload
                    : Array.isArray(payload?.data)
                        ? payload.data
                        : [];
                        
                return list.map((item) => {
                    if (item?.value) {
                        return {
                            value: item.value,
                            label: item.label ?? item.value,
                        };
                    }

                    return {
                        value: item.label,
                        label: item.label,
                    };
                });
            } catch (error) {
                console.error('Failed to fetch Tagify data:', error);
                return [];
            }
        },
        mergeData(source = [], fetched = []) {
            const sourceList = this.normalizeWhitelist(source);
            const fetchedList = this.normalizeWhitelist(fetched);

            const mergedObj = {};
            [...sourceList, ...fetchedList].forEach(item => {
                if (item?.value && !mergedObj[item.value]) {
                    mergedObj[item.value] = item;
                }
            });

            return Object.values(mergedObj);
        },
        applyWhitelist(mergedWhitelist) {
            this.apiData = mergedWhitelist;
            this.updateFilteredData();
            if (!this.tagify) return;

            this.tagify.settings.whitelist = mergedWhitelist;
            this.tagify.settings.dropdown.enabled = 0;
            this.tagify.settings.enforceWhitelist = !!this.enforceWhitelist;
        },

        normalizeWhitelist(list) {
            return (list || [])
                .map(item => {
                    if (typeof item === 'string') {
                        return { value: item, label: item };
                    }
                    if (typeof item === 'object' && item !== null) {
                        const value = item.value ?? item.name ?? item.label;
                        const label = item.label ?? value;
                        return value ? { value, label } : null;
                    }
                    return null;
                })
                .filter(Boolean);
        },
        onTagifyChange(event) {
            const rawValue = event?.detail?.value ?? '';
            if (!rawValue) {
                this.$emit('update:modelValue', []);
                return;
            }

            try {
                const parsed = JSON.parse(rawValue);
                const value = Array.isArray(parsed) ? parsed.map(tag => tag.value) : [];
                this.$emit('update:modelValue', value);
            } catch (error) {
                console.error('Failed to parse Tagify value:', error);
                this.$emit('update:modelValue', []);
            }
        },
        onTagifyInput(event) {
            const value = (event?.detail?.value ?? '').trim();
            this.handleSearch(value);
        },
        syncModelValue(value) {
            if (!this.tagify) return;
            this.tagify.removeAllTags();
            if (value && value.length) {
                this.tagify.addTags(value);
            }
        },
        handleSearch(eventOrValue) {
            const value = typeof eventOrValue === 'string'
                ? eventOrValue
                : (eventOrValue?.target?.value ?? '');

            this.searchQuery = value;
            this.updateFilteredData();
        },
        updateFilteredData() {
            const query = this.searchQuery.trim().toLowerCase();
            if (!query) {
                this.filteredData = [...this.apiData];
                return;
            }

            this.filteredData = this.apiData.filter(item => {
                const label = String(item?.label ?? '').toLowerCase();
                const value = String(item?.value ?? '').toLowerCase();
                return label.includes(query) || value.includes(query);
            });
        },
        selectItem(item) {
            if (!this.tagify || !item?.value) return;
            this.tagify.addTags(item.value);
            this.searchQuery = '';
            if (this.tagify?.DOM?.input) {
                this.tagify.DOM.input.value = '';
            }
            this.updateFilteredData();
        }
    },
    beforeUnmount() {
        if (this.tagify && typeof this.tagify.destroy === 'function') {
            this.tagify.destroy();
        }
    }
}
</script>

<template>
    <Dropdown align="right">
        <template #trigger>
            <input
                ref="input"
                :name="name"
                :placeholder="placeholder"
                :class="classes"
                class="w-full"
                @input="handleSearch"
            />
        </template>

        <template #content>
            <div>
                <DropdownLink
                    as="button"
                    v-for="item in filteredData"
                    :key="item.value"
                    @click.prevent="selectItem(item)"
                >
                    <div class="flex items-center gap-1">
                        <span>{{ item.label }}</span>
                    </div>
                </DropdownLink>
            </div>
        </template>
    </Dropdown>
</template>
