<template>
    <div v-if="topText" class="relative shadow-xs active:scale-95 transition-all duration-200 rounded-xl uppercase text-xs font-semibold flex items-center justify-center" :class="classes">
        <span class="absolute -top-1.5 -right-1 px-1.5 py-0.5 shadow-md bg-lime-600 text-white text-[0.6rem] font-bold rounded-full z-10">{{topText}}</span>
        <button class="w-full h-full flex items-center justify-center"><slot /></button>
    </div>
    <button v-else :class="classes" class="shadow-xs active:scale-95 transition-all duration-200 rounded-xl uppercase text-xs font-semibold flex items-center justify-center"><slot /></button>
</template>
<script setup>

defineProps({
  classes: {
    type: String,
    required: false,
  },
    topText: {
        type: [String, Number],
        default: null
    }
})
</script>
