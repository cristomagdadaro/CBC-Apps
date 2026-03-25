<script>
export default {
    name: 'ResourceSelectionStep',
    props: {
        config: {
            type: Object,
            required: true,
        },
        modelValue: {
            type: Array,
            default: () => [],
        },
        error: {
            type: String,
            default: '',
        },
    },
    emits: ['update:modelValue'],
};
</script>

<template>
    <div class="flex flex-col gap-2">
        <p class="text-sm text-gray-600">{{ config.description }}</p>
        <h2>
            <span class="font-bold uppercase">{{ config.title }}: </span><span class="text-sm">Type to SEARCH and press ENTER to select</span>
        </h2>
        <TagifyInput
            :model-value="modelValue"
            :name="config.field"
            :placeholder="config.placeholder"
            :api-link="config.apiLink"
            :whitelist="config.whitelist"
            :params="config.params"
            @update:modelValue="$emit('update:modelValue', $event)"
        />
        <InputError v-if="error" :message="error" />
    </div>
</template>