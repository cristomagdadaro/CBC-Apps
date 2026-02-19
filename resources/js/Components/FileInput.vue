<script>
export default {
    name: "FileInput",
    props: {
        modelValue: [String, Number],
        autocomplete: String,
        placeholder: String,
        error: String,
        type: String,
        classes: String,
        id: String,
        label: String,
        required: Boolean,
        fileType: String,
    },
    computed: {
        acceptOnly() {
            if (this.fileType === "image") {
                return "image/png, image/gif, image/jpeg"
            }
        }
    }
}
</script>

<template>
    <div class="w-full relative" :class="classes">
        <div v-if="label" class="text-xs text-gray-700 flex items-center justify-between">
            <span class="flex gap-0.5 whitespace-nowrap">{{ label }} <b v-if="required" class="text-red-500 ">*</b></span>
            <transition-container type="slide-bottom">
                <InputError v-show="!!error" class="" :message="error" />
            </transition-container>
        </div>
        <input
            :id="id"
            :name="id"
            :class="{'border-red-500': error}"
            :autocomplete="autocomplete"
            class="w-full placeholder:text-gray-300 focus:border-AB focus:ring-AB"
            :value="modelValue"
            :placeholder="placeholder"
            type="file"
            :accept="acceptOnly"
            @change="$emit('update:modelValue', $event.target.value)"
        >
    </div>
</template>

<style scoped>

</style>
