<template>
    <div class="flex flex-col gap-0.5">
        <span class="text-[0.68rem] sm:text-xs font-semibold text-slate-500 dark:text-slate-400">Search: </span>
        <div class="flex flex-row items-center justify-between gap-0.5 bg-slate-50 dark:bg-slate-800/80 rounded-xl border border-slate-300 dark:border-slate-700 focus-within:border-lime-500 focus-within:ring-1 focus-within:ring-lime-500 transition-all overflow-hidden">
            <input autocomplete="off" class="border-0 py-1.5 px-3 w-full text-xs sm:text-sm bg-transparent text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:ring-0"
                   type="text" id="dtSearch"
                   v-model="search"
                   :placeholder="placeholder"
                   @keyup.capture.delete="!modelValue?.length ? searchBy(null) : null"
                   @keyup.capture.enter="searchBy($event)" />
            <button v-if="modelValue" class="p-1.5 rounded-r hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-90 transition-all text-slate-500 dark:text-slate-400" @click="clearSearch"><close-icon class="h-4 w-4"/> </button>
            <button v-else class="p-1.5 rounded-r hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-90 transition-all text-slate-500 dark:text-slate-400" @click="searchBy"><search-icon class="h-4 w-4" /></button>
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
            default: 'Find it here...',
        }
    },
    data() {
        return {
            search: this.modelValue,
        }
    },
    methods: {
        searchBy(val = null) {
            if (!val || !val.target) {
                this.$emit('searchString', null);
                return;
            }
            this.search = val.target.value;
            this.$emit('searchString', this.search);
        },
        clearSearch(){
            this.search = '';
            this.searchBy();
        }
    },
    watch: {
        modelValue(newVal) {
            this.search = newVal;
        }
    }
}
</script>
