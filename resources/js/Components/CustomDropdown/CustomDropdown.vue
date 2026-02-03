<template>
    <div class="flex flex-col gap-0.5 w-full">
        <div v-if="label" class="text-xs text-gray-500 flex items-center justify-between">
            <span class="flex gap-0.5 whitespace-nowrap">{{ label }}<b v-if="required" class="text-red-500 select-none">*</b></span>
            <transition-container type="slide-bottom">
                <InputError v-show="!!error" class="" :message="error" />
            </transition-container>
        </div>
        <div>
            <div class="w-full focus-within:ring-1 flex gap-1 justify-between border-gray-700 items-center bg-white rounded px-4 py-2 border" @click.prevent="toggle">
                <div v-if="!searchable" class="text-gray-600 whitespace-nowrap overflow-hidden overflow-ellipsis">{{ selected? selected.label : placeholder }}</div>
                <input v-else type="text" @keydown.esc="search = null" @keydown="filterOptions()" v-model="search" class="w-full text-gray-600 border-none focus:outline-none focus:border-transparent focus:ring-0 p-0" :placeholder="selected? selected.label : placeholder" />
                <div class="flex gap-2 items-center">
                    <close-icon class="h-5 w-5" v-if="selected && showClear" @click.prevent="select(null)" />
                    <slot name="icon" :class="open?'rotate-180':'rotate-360'" class="h-4 w-4 duration-300" />
                </div>
            </div>
            <div v-show="open" class="fixed inset-0 z-48" @click.prevent="open = false" />
            <transition-container>
                <div
                    v-show="open"
                    class="z-50 absolute border shadow rounded bg-white mt-1 py-2 max-h-[30vh] overflow-hidden overflow-y-auto py-2"
                >
                    <div v-if="filteredOptions" class="hidden text-xs text-gray-700 px-2 shadow-lg">Options</div>
                    <dropdown-option v-if="!filteredOptions.length">No options available</dropdown-option>
                    <template v-else>
                        <dropdown-option v-if="withAllOption" @click.prevent="select({name:null, label:'All fields'})" :selected="selected && selected.name === defaultOption.name">All fields</dropdown-option>
                        <dropdown-option v-for="option in filteredOptions" @click.prevent="select(option)" v-bind:key="option.label" :selected="option.name === value">
                            {{ option.label }}
                        </dropdown-option>
                    </template>
                </div>
            </transition-container>
        </div>
    </div>
</template>
<script>
import TransitionContainer from "@/Components/CustomDropdown/Components/TransitionContainer.vue";
import CaretDown from "@/Components/Icons/CaretDown.vue";
import CaretUp from "@/Components/Icons/CaretUp.vue";
import DropdownOption from "@/Components/CustomDropdown/Components/DropdownOption.vue";
import CloseIcon from "@/Components/Icons/CloseIcon.vue";
import TextField from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";

export default {
    components: {InputError, TextField, CloseIcon, DropdownOption, CaretUp, CaretDown, TransitionContainer},
    props: {
        searchable: {
            type: Boolean,
            required: false,
            default: false,
        },
        label: {
            type: String,
            required: false,
        },
        withAllOption: {
            type: Boolean,
            required: false,
            default: true,
        },
        placeholder: {
            type: String,
            required: false,
        },
        options: {
            type: Object,
            required: false,
        },
        value: {
            type: [String, Number],
            required: false,
        },
        showClear: {
            type: Boolean,
            default: true,
        },
        required: {
            type: Boolean,
            default: false,
        },
        error: String,
    },
    data(){
        return {
            open: false,
            defaultOption: {name: null, label: 'All fields', selected: true},
            selected: null,
            search: null,
            filteredOptions: [],
        }
    },
    methods: {
        toggle(){
            this.open = !this.open;
        },
        select(option){
            if (option){
                this.$emit('selectedChange', option.name);
            } else {
                this.$emit('selectedChange', null);
            }
            this.search = option? option.label : null;
            this.selected = option;
            this.open = false;
        },
        selectByValue(value, silent = false) {
            this.selected = this.options.find(option => option.name === value);

            if (!silent) {
                this.$emit('selectedChange', this.selected ? this.selected.name : null);
            }
        },
        filterOptions(){
            if (this.search)
                this.filteredOptions = this.options.filter(option => option.label.toLowerCase().includes(this.search.toLowerCase()));
            else
                this.filteredOptions = this.options;
        }
    },
    watch: {
        'options': {
            handler(){
                // If value is given, move the selected option to the top
                if (this.value !== undefined && this.value !== null) {
                    const selectedOption = this.options.find(option => option.name === this.value);
                    if (selectedOption) {
                        this.selected = selectedOption;
                        this.filteredOptions = [selectedOption, ...this.options.filter(option => option.name !== this.value)];
                        return;
                    }
                }
                this.selected = this.options.find(option => option.selected) || null;
                this.filteredOptions = this.options;
            },
            deep: true,
        },
        'value': {
            handler(newVal) {
                if (newVal !== undefined && newVal !== null) {
                    const selectedOption = this.options.find(option => option.name === newVal);
                    if (selectedOption) {
                        this.selected = selectedOption;
                        this.filteredOptions = [selectedOption, ...this.options.filter(option => option.name !== newVal)];
                        return;
                    }
                }
                this.selected = this.options.find(option => option.selected) || null;
                this.filteredOptions = this.options;
            },
            immediate: true
        },
    },
    mounted() {
        if (!this.options) return;
        if (this.value !== undefined && this.value !== null) {
            const selectedOption = this.options.find(option => option.name === this.value);
            if (selectedOption) {
                this.selected = selectedOption;
                this.filteredOptions = [selectedOption, ...this.options.filter(option => option.name !== this.value)];
                return;
            }
        }
        this.selected = this.options.find(option => option.selected) || null;
        this.filteredOptions = this.options;
    }
}
</script>
