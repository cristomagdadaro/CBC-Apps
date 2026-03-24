<template>
    <tr>
        <td :colspan="props.colspan" class="text-center py-2">
            <div class="flex items-center justify-center gap-1">
                <exclamation-circle-icon class="w-5 h-auto" />
                <span>No data found</span>
            </div>
        </td>
    </tr>
</template>
<script setup>
import ExclamationCircleIcon from "@/Components/Icons/ExclamationCircleIcon.vue";
const props = defineProps({
    colspan: Number,
});
</script>
