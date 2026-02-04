<script>
import CloseIcon from "@/Components/Icons/CloseIcon.vue";

export default {
    name: "searchBox",
    components: {CloseIcon},
    props: {
        modelValue: String|Number,
    },
    methods: {
        clearSearch(){
            this.$emit('update:modelValue', null);
            document.getElementById('searchbox').focus()
        },
        updateSearch(event){
            this.$emit('update:modelValue', event.target.value);
        }
    }
}
</script>

<template>
    <div class="flex flex-col gap-0.5">
        <div class="flex justify-between  whitespace-nowrap gap-3">
            <label for="searchbox" class="text-gray-600 text-xs">Search</label>
            <span class="text-gray-500 text-xs">Scan / Type to search</span>
        </div>
        <div class="relative">
            <div v-if="modelValue" @click="clearSearch" class="absolute right-0 h-full flex items-center justify-center" >
                <close-icon class="w-7 h-auto text-gray-600 mr-3 hover:bg-gray-100 rounded hover:scale-110 duration-100 active:scale-100" @click="$emit('update:modelValue', '')" />
            </div>
            <input class="rounded-md drop-shadow border focus:outline-none focus:ring-0 w-full overflow-hidden overflow-ellipsis"
                type="text"
                name="search"
                id="searchbox"
                autocomplete="off"
                :value="modelValue"
                @keyup="updateSearch($event)">
        </div>
    </div>
</template>
