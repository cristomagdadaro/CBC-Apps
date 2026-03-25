<script>
export default {
    name: 'PaginationControls',
    props: {
        currentPage: {
            type: Number,
            default: 1,
        },
        lastPage: {
            type: Number,
            default: 1,
        },
    },
    emits: ['change'],
    methods: {
        emitChange(page) {
            if (page < 1 || page > this.lastPage || page === this.currentPage) {
                return;
            }

            this.$emit('change', page);
        },
    },
};
</script>

<template>
    <div id="dtPaginatorContainer" class="flex gap-1 items-center w-full justify-center">
        <paginate-btn @click="emitChange(1)" :disabled="currentPage === 1">
            First
        </paginate-btn>

        <paginate-btn @click="emitChange(currentPage - 1)" :disabled="currentPage === 1">
            Prev
        </paginate-btn>

        <div class="text-xs flex flex-col whitespace-nowrap text-center">
            <span class="font-medium mx-1" title="current page and total pages">
                <span>{{ currentPage }}</span> / <span>{{ lastPage }}</span>
            </span>
        </div>

        <paginate-btn @click="emitChange(currentPage + 1)" :disabled="currentPage === lastPage">
            Next
        </paginate-btn>

        <paginate-btn @click="emitChange(lastPage)" :disabled="currentPage === lastPage">
            Last
        </paginate-btn>
    </div>
</template>