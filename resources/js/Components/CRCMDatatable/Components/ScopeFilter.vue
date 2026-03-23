<script>

import CaretDown from "@/Components/Icons/CaretDown.vue";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";

export default {
    name: "ScopeFilter",
    emits: ['changeScopeFilter'],
    components: {CaretDown, CustomDropdown},
    props: {
        value: [String, Number],
    },
    data(){
        return {
            scopeFilter: [
                {label: 'Owned Data', name: 'scopeOwnedByUser', selected: true},
                {label: 'Institute\'s Data', name: 'scopeOwnedByAffiliation', selected: false},
                {label: 'Public Data', name: 'scopeOwnedPublic', selected: false},
            ],
        }
    },
}
</script>

<template>
    <div class="flex flex-col gap-0.5">
        <div class="text-xs text-gray-500 flex items-center justify-between">
            <span class="flex gap-0.5 whitespace-nowrap">Scope Filter</span>
        </div>
        <custom-dropdown :show-clear="false"  :value="value" :withAllOption="false" :options="scopeFilter" @selectedChange="$emit('changeScopeFilter', $event)">
            <template #icon>
                <caret-down  class="h-4 w-4 text-gray-700" />
            </template>
        </custom-dropdown>
    </div>
</template>

<style scoped>

</style>
