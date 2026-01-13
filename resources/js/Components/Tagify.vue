<script>
import Tagify from '@yaireo/tagify';
import '@yaireo/tagify/dist/tagify.css';
import ApiMixin from "@/Modules/mixins/ApiMixin";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { toRaw } from 'vue';

export default {
    name: "TagifyInput",
    components: {DropdownLink, Dropdown},
    mixins: [ApiMixin],
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
        this.tagify = new Tagify(this.$refs.input, {
            whitelist: this.whitelist,
            enforceWhitelist: this.enforceWhitelist,
            dropdown: { enabled: 1 },
        })

        // initial value
        if (this.modelValue && this.modelValue.length) {
            this.tagify.addTags(this.modelValue)
        }

        // listen for changes
        this.tagify.on('change', e => {
            const value = JSON.parse(e.detail.value).map(tag => tag.value)
            this.$emit('update:modelValue', value)
        })

        const fetched = await this.fetchData();
        this.mergeData(fetched);
    },
    watch: {
        modelValue(newVal) {
            if (!this.tagify) return
            this.tagify.removeAllTags()
            if (newVal && newVal.length) {
                this.tagify.addTags(newVal)
            }
        },
        whitelist(newVal) {
            if (!this.tagify) return;
            const merged = this.normalizeWhitelist(newVal || []);
            this.tagify.settings.whitelist = merged;
            this.tagify.settings.dropdown.enabled = merged.length ? 1 : 0;
            if (this.enforceWhitelist) this.tagify.settings.enforceWhitelist = true;
            this.apiData = merged;
        }
    },
    data() {
        return {
            apiData: [],
            tagify: null,
        }
    },
    methods: {
        async fetchData() {
            if (!this.apiLink)
                return [];

            const params = {
                filter: 'name',
                per_page: '*',
            }
            const fetched = await this.fetchGetApi(this.apiLink, params).then((response) => {
                return response.data.map((item) => {
                    return {
                        value: item.label,
                        label: item.label,
                    }
                });
            });

            return fetched;
        },
        mergeData(fetched = []) {
            const propList = this.normalizeWhitelist(this.whitelist || []);
            const fetchedList = this.normalizeWhitelist(fetched);

            const mergedObj = {};
            [...propList, ...fetchedList].forEach(item => {
                if (item?.value && !mergedObj[item.value]) {
                    mergedObj[item.value] = item;
                }
            });

            const mergedWhitelist = Object.values(mergedObj);

            this.tagify.settings.whitelist = mergedWhitelist;
            this.tagify.settings.dropdown.enabled = mergedWhitelist.length ? 1 : 0;
            if (this.enforceWhitelist) this.tagify.settings.enforceWhitelist = true;

            this.apiData = mergedWhitelist;
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
        handleSearch(query) {
            console.log(query);
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
    <Dropdown align="right" @input="handleSearch($refs.input)">
        <template #trigger>
            <input
                ref="input"
                :name="name"
                :placeholder="placeholder"
                :class="classes"
                class="w-full"
            />
        </template>

        <template #content>
            <div>
                <DropdownLink as="button" v-for="item in apiData" :key="item.value" @click.prevent="tagify.addTags(item.value)">
                    <div class="flex items-center gap-1">
                        <span>{{ item.label }}</span>
                    </div>
                </DropdownLink>
            </div>
        </template>
    </Dropdown>
</template>
