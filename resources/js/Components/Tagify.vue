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
    props: {
        modelValue: {
            type: [Array, String],
            default: () => []
        },
        placeholder: String,
        classes: String,
        name: String,
        apiLink: String,
    },
    async mounted() {
        this.tagify = new Tagify(this.$refs.input, {
            whitelist: [],
            dropdown: { enabled: 0 },
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

        await this.fetchData();
    },
    watch: {
        modelValue(newVal) {
            if (!this.tagify) return
            this.tagify.removeAllTags()
            if (newVal && newVal.length) {
                this.tagify.addTags(newVal)
            }
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
                return;

            const params = {
                filter: 'name',
                per_page: '*',
            }
            this.apiData = await this.fetchGetApi(this.apiLink, params).then((response) => {
                return response.data.map((item) => {
                    return {
                        value: item.name,
                        label: item.name,
                    }
                });
            });
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
