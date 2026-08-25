<template>
    <tr>
        <td
            :colspan="props.colspan"
            class="py-2 text-center">
            <div class="flex items-center justify-center gap-1">
                <exclamation-circle-icon class="h-auto w-5" />
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
