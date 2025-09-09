<script>
import Tagify from '@yaireo/tagify';
import '@yaireo/tagify/dist/tagify.css';

export default {
    name: "TagifyInput",
    props: {
        modelValue: {
            type: [Array, String],
            default: () => []
        },
        placeholder: String,
        classes: String,
        name: String,
    },
    mounted() {
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
    },
    watch: {
        modelValue(newVal) {
            if (!this.tagify) return
            this.tagify.removeAllTags()
            if (newVal && newVal.length) {
                this.tagify.addTags(newVal)
            }
        }
    }
}
</script>

<template>
    <input
        ref="input"
        :name="name"
        :placeholder="placeholder"
        :class="classes"
    />
</template>
