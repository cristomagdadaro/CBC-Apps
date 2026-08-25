<script>
export default {
    name: "searchBox",
    props: {
        modelValue: String | Number,
    },
    methods: {
        clearSearch() {
            this.$emit("update:modelValue", null);
            document.getElementById("searchbox").focus();
        },
        updateSearch(event) {
            this.$emit("update:modelValue", event.target.value);
        },
    },
};
</script>

<template>
    <div class="flex flex-col gap-0.5">
        <div class="flex justify-between gap-3 whitespace-nowrap">
            <label
                for="searchbox"
                class="text-xs text-gray-600">
                Search
            </label>
            <span class="text-xs text-gray-500">Scan / Type to search</span>
        </div>
        <div class="relative">
            <div
                v-if="modelValue"
                @click="clearSearch"
                class="absolute right-0 flex h-full items-center justify-center">
                <close-icon
                    class="mr-3 h-auto w-7 rounded text-gray-600 duration-100 hover:scale-110 hover:bg-gray-100 active:scale-100"
                    @click="$emit('update:modelValue', '')" />
            </div>
            <input
                class="w-full overflow-hidden overflow-ellipsis rounded-md border drop-shadow focus:outline-none focus:ring-0"
                type="text"
                name="search"
                id="searchbox"
                autocomplete="off"
                :value="modelValue"
                @keyup="updateSearch($event)" />
        </div>
    </div>
</template>
