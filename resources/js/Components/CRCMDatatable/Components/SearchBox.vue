<template>
    <div class="flex flex-col gap-0.5">
        <span class="text-[0.68rem] font-semibold text-slate-500 sm:text-xs dark:text-slate-400">Search:</span>
        <div class="flex flex-row items-center justify-between gap-0.5 overflow-hidden rounded-xl border border-slate-300 bg-slate-50 transition-all focus-within:border-lime-500 focus-within:ring-1 focus-within:ring-lime-500 dark:border-slate-700 dark:bg-slate-800/80">
            <input
                autocomplete="off"
                class="w-full border-0 bg-transparent px-3 py-1.5 text-xs text-slate-900 placeholder-slate-400 focus:ring-0 sm:text-sm dark:text-slate-100"
                type="text"
                id="dtSearch"
                v-model="search"
                :placeholder="placeholder"
                @keyup.capture.delete="!modelValue?.length ? searchBy(null) : null"
                @keyup.capture.enter="searchBy($event)" />
            <button
                v-if="modelValue"
                class="rounded-r p-1.5 text-slate-500 transition-all hover:bg-slate-200 active:scale-90 dark:text-slate-400 dark:hover:bg-slate-700"
                @click="clearSearch">
                <close-icon class="h-4 w-4" />
            </button>
            <button
                v-else
                class="rounded-r p-1.5 text-slate-500 transition-all hover:bg-slate-200 active:scale-90 dark:text-slate-400 dark:hover:bg-slate-700"
                @click="searchBy">
                <search-icon class="h-4 w-4" />
            </button>
        </div>
    </div>
</template>
<script>
import SearchIcon from "@/Components/Icons/SearchIcon.vue";
import CloseIcon from "@/Components/Icons/CloseIcon.vue";
export default {
    components: {
        CloseIcon,
        SearchIcon,
    },
    props: {
        modelValue: {
            type: String,
            required: false,
        },
        placeholder: {
            type: String,
            default: "Find it here...",
        },
    },
    data() {
        return {
            search: this.modelValue,
        };
    },
    methods: {
        searchBy(val = null) {
            if (!val || !val.target) {
                this.$emit("searchString", null);
                return;
            }
            this.search = val.target.value;
            this.$emit("searchString", this.search);
        },
        clearSearch() {
            this.search = "";
            this.searchBy();
        },
    },
    watch: {
        modelValue(newVal) {
            this.search = newVal;
        },
    },
};
</script>
