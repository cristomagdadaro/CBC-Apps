<template>
    <div v-if="!resolvedIndexEndpoint" class="p-4 text-center text-red-600 bg-red-50 rounded-lg border border-red-200">
        <alert-circle-icon class="w-12 h-12 mx-auto mb-2 opacity-50" />
        <p class="font-medium">Configuration Error</p>
        <p class="text-sm">Unable to retrieve data. Please check model endpoints.</p>
    </div>

    <div v-else-if="!canView" class="p-8 text-center text-gray-500">
        <shield-icon class="w-16 h-16 mx-auto mb-3 opacity-30" />
        <p class="text-lg font-medium">Access Denied</p>
        <p class="text-sm">You don't have permission to view this data.</p>
    </div>

    <div v-else-if="dt instanceof CRCMDatatable" id="dtContainer"
        :class="['flex flex-col gap-3 p-2 sm:p-4 overflow-visible transition-colors duration-300', presetClasses.container]">

        <!-- Top Bar: Filters & Actions -->
        <div
            class="relative z-30 flex flex-col lg:flex-row gap-3 justify-between items-start lg:items-center bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm rounded-xl p-3 border border-gray-200 dark:border-gray-700 shadow-sm">

            <!-- Left: Filters Section -->
            <div class="flex flex-col sm:flex-row gap-2 w-full lg:w-auto items-start sm:items-center flex-wrap">
                <!-- Default Filters -->
                <div class="flex flex-col sm:flex-row gap-2 w-full">
                    <per-page :value="dt.request.getPerPage" @changePerPage="dt.perPageFunc({ per_page: $event })"
                        :theme="colorPreset" />

                    <search-by :value="dt.request.getFilter" :is-exact="dt.request.getIsExact" :options="dt.columns"
                        @isExact="dt.isExactFilter({ is_exact: $event })"
                        @searchBy="dt.filterByColumn({ column: $event })" :theme="colorPreset" />

                    <search-filter :value="dt.request.getSearch" @searchString="dt.searchFunc({ search: $event })"
                        class="w-full" :theme="colorPreset" />

                    <scope-filter v-if="showScopeFilter" :value="dt.request.getScope"
                        @change-scope-filter="dt.scopeBy({ 'scope_by': $event })" :theme="colorPreset" />
                </div>

                <!-- Custom Filters Slot -->
                <div class="flex flex-wrap gap-2 items-center">
                    <slot name="custom-filters" :datatable="dt" :customFilters="dt.request" :refresh="() => dt.refresh()" />
                </div>
            </div>

            <!-- Right: Actions Section -->
            <action-container class="w-full lg:w-auto flex-wrap sm:flex-nowrap gap-1.5">
                <!-- Theme Selector -->
                <div class="relative group">
                    <top-action-btn @click="showThemeMenu = !showThemeMenu" :class="presetClasses.secondaryBtn"
                        title="Change Theme">
                        <template #icon>
                            <palette-icon class="w-4 h-4 sm:w-5 sm:h-5" />
                        </template>
                        <span v-show="showIconText">Theme</span>
                    </top-action-btn>

                    <!-- Theme Dropdown -->
                    <transition enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 scale-95" enter-to-class="opacity-100 scale-100">
                        <div v-if="showThemeMenu"
                            class="absolute right-0 mt-2 w-40 z-[80] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                            <button v-for="(preset, key) in colorPresets" :key="key" @click="setColorPreset(key)"
                                class="w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2"
                                :class="{ 'bg-gray-50 dark:bg-gray-700/50': colorPreset === key }">
                                <div class="w-3 h-3 rounded-full" :class="preset.indicator"></div>
                                <span class="capitalize">{{ key }}</span>
                            </button>
                        </div>
                    </transition>
                </div>

                <!-- Custom Actions Slot -->
                <slot name="custom-actions" :datatable="dt" :selected="dt.selected" :processing="dt.processing" />

                <!-- Standard Actions -->
                <top-action-btn v-if="showActionBtns && canCreate" @click="showAddDialogFunc()"
                    :class="presetClasses.primaryBtn" title="Add new record">
                    <template #icon>
                        <plus-icon class="w-4 h-4 sm:w-5 sm:h-5" />
                    </template>
                    <span v-show="showIconText">Add</span>
                </top-action-btn>

                <top-action-btn @click="dt.refresh()"
                    :class="dt.processing ? 'opacity-75 cursor-not-allowed' : presetClasses.secondaryBtn"
                    :disabled="dt.processing" title="Refresh data">
                    <template #icon>
                        <refresh-cw-icon class="w-4 h-4 sm:w-5 sm:h-5" :class="{ 'animate-spin': dt.processing }" />
                    </template>
                    <span v-show="showIconText">Refresh</span>
                </top-action-btn>

                <top-action-btn v-if="canDelete && dataDb.length && dt.selected.length && showActionBtns"
                    @click="showDeleteSelectedDialogFunc()"
                    class="bg-red-600 hover:bg-red-700 text-white shadow-red-200" title="Delete selected">
                    <template #icon>
                        <trash-2-icon class="w-4 h-4 sm:w-5 sm:h-5" />
                    </template>
                    <span v-show="showIconText" class="hidden sm:inline">Delete ({{ dt.selected.length }})</span>
                </top-action-btn>

                <top-action-btn v-if="dataDb.length && showActionBtns" :class="presetClasses.ghostBtn"
                    @click="dt.selectAll()" :top-text="dt.selected.length || null" title="Select all visible">
                    <template #icon>
                        <check-square-icon class="w-4 h-4 sm:w-5 sm:h-5" />
                    </template>
                    <span v-show="showIconText">Select All</span>
                </top-action-btn>

                <top-action-btn v-if="selected.length && dataDb.length && showActionBtns"
                    :class="presetClasses.ghostBtn" @click="dt.deselectAll()" title="Clear selection">
                    <template #icon>
                        <square-icon class="w-4 h-4 sm:w-5 sm:h-5" />
                    </template>
                    <span v-show="showIconText">Clear</span>
                </top-action-btn>

                <top-action-btn v-if="dataDb.length && showActionBtns && canView" :class="presetClasses.secondaryBtn"
                    @click="dt.exportCSV()" title="Export CSV">
                    <template #icon>
                        <file-down-icon class="w-4 h-4 sm:w-5 sm:h-5" />
                    </template>
                    <span v-show="showIconText">Export</span>
                </top-action-btn>

                <top-action-btn v-if="showActionBtns && canCreate" :class="presetClasses.secondaryBtn"
                    @click="showImportModal = true" title="Import CSV">
                    <template #icon>
                        <upload-icon class="w-4 h-4 sm:w-5 sm:h-5" />
                    </template>
                    <span v-show="showIconText">Import</span>
                </top-action-btn>

                <top-action-btn @click="toggleIconText" :class="presetClasses.ghostBtn" title="Toggle labels">
                    <template #icon>
                        <type-icon v-if="!showIconText" class="w-4 h-4 sm:w-5 sm:h-5" />
                        <toggle-right-icon v-else class="w-4 h-4 sm:w-5 sm:h-5" />
                    </template>
                </top-action-btn>
            </action-container>
        </div>

        <!-- Table Container -->
        <div id="dtTableContainer"
            class="relative z-10 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Loading Overlay -->
            <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="dt.processing"
                    class="absolute inset-0 bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm z-40 flex flex-col items-center justify-center gap-3">
                    <loader-2-icon class="w-10 h-10 animate-spin" :class="presetClasses.textPrimary" />
                    <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Loading data...</span>
                </div>
            </transition>

            <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 z-10">
                <table id="dtTable" class="w-full text-sm text-left">
                    <crcm-thead :class="presetClasses.headerBg">
                        <thead-row>
                            <th class="w-10 p-3 text-center">
                                <span class="sr-only">Select</span>
                            </th>
                            <th v-for="column in dt.model.getColumns()" :key="column.key + column.title"
                                class="p-3 font-semibold text-xs uppercase tracking-wider whitespace-nowrap cursor-pointer select-none transition-colors "
                                :class="[
                                    column.sortable ? 'hover:bg-black/5 dark:hover:bg-white/5' : '',
                                    column.visible !== false ? '' : 'hidden',
                                    getSortClasses(column)
                                ]" @click="onColumnSort(column)">
                                <div class="flex items-center gap-1.5" :class="column.align ? column.align : 'text-left'">
                                    <span>{{ column.title }}</span>
                                    <span v-if="column.sortable" class="text-[10px] opacity-50">
                                        <arrow-up-icon
                                            v-if="dt.request.getSort === column.key && dt.request.getParam('order') === 'asc'"
                                            class="w-3 h-3" />
                                        <arrow-down-icon
                                            v-else-if="dt.request.getSort === column.key && dt.request.getParam('order') === 'desc'"
                                            class="w-3 h-3" />
                                        <more-horizontal-icon v-else class="w-3 h-3 opacity-0 group-hover:opacity-50" />
                                    </span>
                                </div>
                            </th>
                            <th v-if="showActionBtns" class="p-3 text-right text-xs uppercase tracking-wider">
                                Actions
                            </th>
                        </thead-row>
                    </crcm-thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template v-if="!dt.processing">
                            <tr v-if="dataDb.length === 0">
                                <td :colspan="dt.model.getColumns().length + 2" class="p-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center gap-2">
                                        <search-x-icon class="w-12 h-12 opacity-20" />
                                        <p class="font-medium">No records found</p>
                                        <p class="text-xs">Try adjusting your filters or search terms</p>
                                    </div>
                                </td>
                            </tr>

                            <tr v-for="row in dataDb" :key="row.id"
                                class="group transition-colors hover:bg-gray-50 dark:hover:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700 last:border-0"
                                :class="[dt.isSelected(row.id) ? presetClasses.selectedRow : '']"
                                @contextmenu.prevent="showContextMenu($event, row)">

                                <!-- Selection Cell -->
                                <td class="p-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span class="text-xs text-gray-400 font-mono w-6 text-right">{{ meta_from +
                                            dataDb.indexOf(row) }}</span>
                                        <input type="checkbox" :checked="dt.isSelected(row.id)"
                                            :disabled="!isRowDeletable(row)" @click.stop="dt.addSelected(row.id)"
                                            class="w-4 h-4 rounded border-gray-300 text-current focus:ring-offset-0 focus:ring-2 transition-all disabled:opacity-50"
                                            :class="presetClasses.checkbox">
                                    </div>
                                </td>

                                <!-- Data Cells -->
                                <td v-for="column in visibleColumns" :key="column.key"
                                    class="p-3 text-gray-700 dark:text-gray-300 max-w-xs truncate"
                                    :class="[column.align || 'text-left', column.visible === false ? 'hidden' : '']"
                                    @dblclick="dt.addSelected(row.id)" @click.ctrl="dt.addSelected(row.id)">
                                    <slot :name="`cell-${column.key}`" :row="row"
                                        :value="getNestedValue(row, column.key)">
                                        {{ getNestedValue(row, column.key) }}
                                    </slot>
                                </td>

                                <!-- Actions Cell -->
                                <td v-if="showActionBtns" class="p-3 text-right">
                                    <div
                                        class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity sm:opacity-100">
                                        <slot name="rowActions" :row="row" :showIconText="showIconText" />

                                        <button v-if="canView && resolvedShowEndpoint"
                                            @click="router.visit(route(resolvedShowEndpoint, { id: row.id }))"
                                            class="p-1.5 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 text-blue-600 dark:text-blue-400 transition-colors"
                                            title="View">
                                            <eye-icon class="w-4 h-4" />
                                            <span v-if="showIconText" class="sr-only">View</span>
                                        </button>

                                        <button v-if="canUpdate && isRowUpdatable(row) && resolvedShowEndpoint"
                                            @click="router.visit(route(resolvedShowEndpoint, { id: row.id }))"
                                            class="p-1.5 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-900/30 text-amber-600 dark:text-amber-400 transition-colors"
                                            title="Edit">
                                            <file-edit-icon class="w-4 h-4" />
                                            <span v-if="showIconText" class="sr-only">Edit</span>
                                        </button>

                                        <button v-if="canDelete && isRowDeletable(row)"
                                            @click="showDeleteDialogFunc(row.id)"
                                            class="p-1.5 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 text-red-600 dark:text-red-400 transition-colors"
                                            title="Delete">
                                            <trash-2-icon class="w-4 h-4" />
                                            <span v-if="showIconText" class="sr-only">Delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Footer Info -->
            <div
                class="flex flex-col sm:flex-row justify-between items-center p-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-xs text-gray-600 dark:text-gray-400 gap-2">
                <div class="flex items-center gap-2">
                    <span>Showing <strong>{{ meta_from }}-{{ meta_to }}</strong> of <strong>{{ total_entries
                            }}</strong></span>
                    <span v-if="dt.selected.length" class="px-2 py-0.5 rounded-full text-white text-[10px] font-medium"
                        :class="presetClasses.badge">
                        {{ dt.selected.length }} selected
                    </span>
                </div>

                <!-- Mobile Pagination -->
                <div class="flex items-center gap-1 sm:hidden">
                    <button @click="dt.prevPage()" :disabled="!prev_page" class="p-2 rounded-lg disabled:opacity-50"
                        :class="presetClasses.ghostBtn">
                        <chevron-left-icon class="w-5 h-5" />
                    </button>
                    <span class="px-3 py-1 text-sm font-medium">{{ current_page }} / {{ total_pages }}</span>
                    <button @click="dt.nextPage()" :disabled="current_page === last_page"
                        class="p-2 rounded-lg disabled:opacity-50" :class="presetClasses.ghostBtn">
                        <chevron-right-icon class="w-5 h-5" />
                    </button>
                </div>

                <!-- Desktop Pagination -->
                <div class="hidden sm:flex items-center gap-1">
                    <button @click="dt.firstPage()" :disabled="current_page === first_page"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        :class="presetClasses.secondaryBtn">
                        First
                    </button>
                    <button @click="dt.prevPage()" :disabled="!prev_page"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-1"
                        :class="presetClasses.secondaryBtn">
                        <chevron-left-icon class="w-3 h-3" /> Prev
                    </button>

                    <div class="flex items-center gap-1 px-2">
                        <input ref="pageInput" type="number" :value="current_page" min="1" :max="total_pages"
                            @keydown.enter="handlePageInput"
                            class="w-12 px-2 py-1 text-center text-xs border rounded-md focus:ring-2 focus:border-transparent bg-transparent"
                            :class="presetClasses.input">
                        <span class="text-gray-400">/</span>
                        <span class="text-xs font-medium">{{ total_pages }}</span>
                    </div>

                    <button @click="dt.nextPage()" :disabled="current_page === last_page"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-1"
                        :class="presetClasses.secondaryBtn">
                        Next <chevron-right-icon class="w-3 h-3" />
                    </button>
                    <button @click="dt.lastPage()" :disabled="current_page === last_page"
                        class="px-3 py-1.5 rounded-lg text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        :class="presetClasses.secondaryBtn">
                        Last
                    </button>
                </div>
            </div>
        </div>

        <!-- Context Menu -->
        <context-menu ref="contextMenu" v-if="rowContextMenu" @close="rowContextMenu = null">
            <div
                class="min-w-[160px] py-1 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700">
                <div
                    class="px-3 py-2 text-xs font-semibold text-gray-500 border-b border-gray-100 dark:border-gray-700 mb-1">
                    Actions
                </div>
                <slot name="rowActionsMenu" :row="rowContextMenu" />

                <button v-if="canView && resolvedShowEndpoint"
                    @click="router.visit(route(resolvedShowEndpoint, { id: rowContextMenu.id }))"
                    class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-700 dark:text-gray-300">
                    <eye-icon class="w-4 h-4 text-blue-500" />
                    View Details
                </button>

                <button v-if="canUpdate && isRowUpdatable(rowContextMenu) && resolvedShowEndpoint"
                    @click="router.visit(route(resolvedShowEndpoint, { id: rowContextMenu.id }))"
                    class="w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-2 text-gray-700 dark:text-gray-300">
                    <file-edit-icon class="w-4 h-4 text-amber-500" />
                    Edit Record
                </button>

                <div v-if="canDelete && isRowDeletable(rowContextMenu)"
                    class="border-t border-gray-100 dark:border-gray-700 mt-1 pt-1">
                    <button @click="showDeleteDialogFunc(rowContextMenu.id); rowContextMenu = null"
                        class="w-full px-3 py-2 text-left text-sm hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2 text-red-600">
                        <trash-2-icon class="w-4 h-4" />
                        Delete
                    </button>
                </div>
            </div>
        </context-menu>

        <!-- Modals remain similar but styled with presets -->
        <dialog-form-modal :show="showImportModal && canCreate" @close="closeDialog">
            <component :is="importModal" v-if="importModal" :processing="dt.processing" :errors="errorBag"
                @uploadForm="dt.importCSV($event)" @close="closeDialog" :forceClose="dt.closeAllModal"
                :theme="colorPreset" />
        </dialog-form-modal>

        <dialog-form-modal :show="showAddDialog && canCreate" @close="closeDialog">
            <component :is="addForm" v-if="addForm" :processing="dt.processing" :errors="errorBag"
                @submitForm="dt.create($event)" @close="closeDialog" :forceClose="dt.closeAllModal"
                :theme="colorPreset" />
        </dialog-form-modal>

        <!-- Delete Confirmation -->
        <dialog-modal :show="showDeleteDialog && canDelete" @close="closeDialog" :processing="dt.processing"
            :forceClose="dt.closeAllModal">
            <template #title>
                <div class="flex items-center gap-2 text-red-600">
                    <alert-triangle-icon class="w-5 h-5" />
                    Confirm Deletion
                </div>
            </template>
            <template #content>
                <div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                    <p>Are you sure you want to delete this record?</p>
                    <div class="p-3 bg-gray-100 dark:bg-gray-800 rounded-lg font-mono text-xs" v-if="toDeleteId">
                        ID: {{ toDeleteId }}
                    </div>
                    <p class="text-xs text-red-500">This action cannot be undone.</p>
                </div>
            </template>
            <template #footer>
                <button @click="closeDialog"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button @click="confirmSingleDelete" :disabled="dt.processing"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 flex items-center gap-2">
                    <trash-2-icon v-if="!dt.processing" class="w-4 h-4" />
                    <loader-2-icon v-else class="w-4 h-4 animate-spin" />
                    Delete
                </button>
            </template>
        </dialog-modal>

        <!-- Bulk Delete Confirmation -->
        <dialog-modal :show="showDeleteSelectedDialog && canDelete" @close="closeDialog" :processing="dt.processing"
            :forceClose="dt.closeAllModal">
            <template #title>
                <div class="flex items-center gap-2 text-red-600">
                    <alert-triangle-icon class="w-5 h-5" />
                    Delete Multiple Records
                </div>
            </template>
            <template #content>
                <div class="text-sm text-gray-600 dark:text-gray-400 space-y-3">
                    <p>You are about to delete <strong>{{ dt.selected.length }}</strong> records:</p>
                    <div
                        class="max-h-32 overflow-y-auto p-2 bg-gray-100 dark:bg-gray-800 rounded text-xs font-mono space-y-1">
                        <div v-for="id in dt.selected.slice(0, 10)" :key="id" class="text-gray-600 dark:text-gray-400">
                            ID: {{ id }}</div>
                        <div v-if="dt.selected.length > 10" class="text-gray-400 italic">... and {{ dt.selected.length -
                            10 }} more</div>
                    </div>
                    <p class="text-xs text-red-500">This action cannot be undone.</p>
                </div>
            </template>
            <template #footer>
                <button @click="closeDialog"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button @click="confirmBulkDelete" :disabled="dt.processing"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-50 flex items-center gap-2">
                    <trash-2-icon v-if="!dt.processing" class="w-4 h-4" />
                    <loader-2-icon v-else class="w-4 h-4 animate-spin" />
                    Delete All
                </button>
            </template>
        </dialog-modal>
    </div>
</template>

<script setup>
// Lucide Icons - assuming globally registered or import specific ones
import {
    Plus, RefreshCw, Trash2, CheckSquare, Square, FileDown, Upload,
    Eye, FileEdit, ChevronLeft, ChevronRight, Loader2, Search, Filter,
    Palette, Type, ToggleRight, ArrowUp, ArrowDown, MoreHorizontal,
    AlertCircle, AlertTriangle, Shield, SearchX
} from 'lucide-vue-next';

import ActionContainer from "@/Components/CRCMDatatable/Layouts/ActionContainer.vue";
import DialogFormModal from "@/Components/CRCMDatatable/Layouts/DialogFormModal.vue";

// Component imports remain similar
import {
    ContextMenu,
    CrcmTable, CrcmTbody, CrcmThead, TheadRow, TbodyRow
} from '@/Components/CRCMDatatable/Components';

import SearchFilter from "@/Components/CRCMDatatable/Components/SearchBox.vue";
import PerPage from "@/Components/CRCMDatatable/Components/PerPage.vue";
import SearchBy from "@/Components/CRCMDatatable/Components/SearchBy.vue";
import ScopeFilter from "@/Components/CRCMDatatable/Components/ScopeFilter.vue";
import TopActionBtn from "@/Components/CRCMDatatable/Components/TopActionBtn.vue";
import DialogModal from "@/Components/DialogModal.vue";
</script>

<script>
import CRCMDatatable from "@/Components/CRCMDatatable/core/CRCMDatatable.js";
import { router } from "@inertiajs/vue3";
import { defineAsyncComponent } from "vue";
import ApiMixin from "@/Modules/mixins/ApiMixin";
import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

// Icon mapping for template usage
const icons = {
    PlusIcon: Plus,
    RefreshCwIcon: RefreshCw,
    Trash2Icon: Trash2,
    CheckSquareIcon: CheckSquare,
    SquareIcon: Square,
    FileDownIcon: FileDown,
    UploadIcon: Upload,
    EyeIcon: Eye,
    FileEditIcon: FileEdit,
    ChevronLeftIcon: ChevronLeft,
    ChevronRightIcon: ChevronRight,
    Loader2Icon: Loader2,
    SearchIcon: Search,
    FilterIcon: Filter,
    PaletteIcon: Palette,
    TypeIcon: Type,
    ToggleRightIcon: ToggleRight,
    ArrowUpIcon: ArrowUp,
    ArrowDownIcon: ArrowDown,
    MoreHorizontalIcon: MoreHorizontal,
    AlertCircleIcon: AlertCircle,
    AlertTriangleIcon: AlertTriangle,
    ShieldIcon: Shield,
    SearchXIcon: SearchX
};

export default {
    name: "CRCMDatatable",
    components: { ...icons },
    mixins: [ApiMixin],
    props: {
        baseModel: { type: [DtoBaseClass, Function], required: false },
        params: { type: Object, required: false, default: () => ({}) },
        importModal: { type: [Object, Function], required: false, default: null },
        addForm: { type: [Object, Function], required: false, default: null },
        showForm: { type: [Object, Function], required: false, default: null },
        showActionBtns: { type: Boolean, default: true },
        showScopeFilter: { type: Boolean, default: false },
        canCreate: { type: Boolean, default: false },
        canUpdate: { type: Boolean, default: false },
        canDelete: { type: Boolean, default: false },
        canView: { type: Boolean, default: false },
        rowCanUpdate: { type: Function, default: null },
        rowCanDelete: { type: Function, default: null },
        defaultColorPreset: { type: String, default: 'emerald' },
    },
    data() {
        return {
            dt: null,
            showModal: false,
            showDeleteSelectedDialog: false,
            showDeleteDialog: false,
            showAddDialog: false,
            showImportModal: false,
            toDeleteId: null,
            rowContextMenu: null,
            showThemeMenu: false,
            showIconText: localStorage.getItem('dt_show_icon_text') !== 'false',
            colorPreset: localStorage.getItem('dt_color_preset') || this.defaultColorPreset,
            clickSortCtr: 0,
        }
    },
    computed: {
        colorPresets() {
            return {
                emerald: {
                    indicator: 'bg-emerald-500',
                    primaryBtn: 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-200 dark:shadow-emerald-900/20',
                    secondaryBtn: 'bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200',
                    ghostBtn: 'hover:bg-gray-100 text-gray-600 dark:hover:bg-gray-700 dark:text-gray-400',
                    headerBg: 'bg-emerald-50/80 dark:bg-emerald-900/20 text-emerald-900 dark:text-emerald-100',
                    selectedRow: 'bg-emerald-50 dark:bg-emerald-900/20',
                    textPrimary: 'text-emerald-600',
                    checkbox: 'text-emerald-600 focus:ring-emerald-500',
                    badge: 'bg-emerald-600',
                    input: 'border-gray-300 focus:ring-emerald-500',
                    container: ''
                },
                blue: {
                    indicator: 'bg-blue-500',
                    primaryBtn: 'bg-blue-600 hover:bg-blue-700 text-white shadow-blue-200 dark:shadow-blue-900/20',
                    secondaryBtn: 'bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200',
                    ghostBtn: 'hover:bg-gray-100 text-gray-600 dark:hover:bg-gray-700 dark:text-gray-400',
                    headerBg: 'bg-blue-50/80 dark:bg-blue-900/20 text-blue-900 dark:text-blue-100',
                    selectedRow: 'bg-blue-50 dark:bg-blue-900/20',
                    textPrimary: 'text-blue-600',
                    checkbox: 'text-blue-600 focus:ring-blue-500',
                    badge: 'bg-blue-600',
                    input: 'border-gray-300 focus:ring-blue-500',
                    container: ''
                },
                purple: {
                    indicator: 'bg-purple-500',
                    primaryBtn: 'bg-purple-600 hover:bg-purple-700 text-white shadow-purple-200 dark:shadow-purple-900/20',
                    secondaryBtn: 'bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200',
                    ghostBtn: 'hover:bg-gray-100 text-gray-600 dark:hover:bg-gray-700 dark:text-gray-400',
                    headerBg: 'bg-purple-50/80 dark:bg-purple-900/20 text-purple-900 dark:text-purple-100',
                    selectedRow: 'bg-purple-50 dark:bg-purple-900/20',
                    textPrimary: 'text-purple-600',
                    checkbox: 'text-purple-600 focus:ring-purple-500',
                    badge: 'bg-purple-600',
                    input: 'border-gray-300 focus:ring-purple-500',
                    container: ''
                },
                orange: {
                    indicator: 'bg-orange-500',
                    primaryBtn: 'bg-orange-600 hover:bg-orange-700 text-white shadow-orange-200 dark:shadow-orange-900/20',
                    secondaryBtn: 'bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 dark:text-gray-200',
                    ghostBtn: 'hover:bg-gray-100 text-gray-600 dark:hover:bg-gray-700 dark:text-gray-400',
                    headerBg: 'bg-orange-50/80 dark:bg-orange-900/20 text-orange-900 dark:text-orange-100',
                    selectedRow: 'bg-orange-50 dark:bg-orange-900/20',
                    textPrimary: 'text-orange-600',
                    checkbox: 'text-orange-600 focus:ring-orange-500',
                    badge: 'bg-orange-600',
                    input: 'border-gray-300 focus:ring-orange-500',
                    container: ''
                },
                dark: {
                    indicator: 'bg-gray-800',
                    primaryBtn: 'bg-gray-800 hover:bg-gray-900 text-white shadow-gray-400 dark:shadow-black/20',
                    secondaryBtn: 'bg-gray-200 hover:bg-gray-300 text-gray-800 dark:bg-gray-600 dark:hover:bg-gray-500 dark:text-gray-200',
                    ghostBtn: 'hover:bg-gray-200 text-gray-700 dark:hover:bg-gray-700 dark:text-gray-400',
                    headerBg: 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100',
                    selectedRow: 'bg-gray-100 dark:bg-gray-700/50',
                    textPrimary: 'text-gray-800',
                    checkbox: 'text-gray-800 focus:ring-gray-700',
                    badge: 'bg-gray-800',
                    input: 'border-gray-400 focus:ring-gray-700',
                    container: ''
                }
            };
        },
        presetClasses() {
            return this.colorPresets[this.colorPreset] || this.colorPresets.emerald;
        },
        // ... other computed properties remain the same as original
        isAuthenticated() { return !!this.$page?.props?.auth?.user; },
        modelEndpoints() { return this.baseModel?.endpoints || {}; },
        resolvedIndexEndpoint() {
            if (this.isAuthenticated) return this.modelEndpoints.indexAuth || this.modelEndpoints.index || this.modelEndpoints.indexGuest || null;
            return this.modelEndpoints.indexGuest || this.modelEndpoints.index || this.modelEndpoints.indexAuth || null;
        },
        resolvedPostEndpoint() {
            if (this.isAuthenticated) return this.modelEndpoints.postAuth || this.modelEndpoints.post || this.modelEndpoints.postGuest || null;
            return this.modelEndpoints.postGuest || this.modelEndpoints.post || this.modelEndpoints.postAuth || null;
        },
        resolvedPutEndpoint() { return this.modelEndpoints.put || null; },
        resolvedDeleteEndpoint() { return this.modelEndpoints.delete || null; },
        resolvedDeleteManyEndpoint() {
            return this.modelEndpoints.deleteMany
                || this.modelEndpoints.multiDestroy
                || (this.resolvedDeleteEndpoint ? this.resolvedDeleteEndpoint.replace('.destroy', '.multi-destroy') : null);
        },
        resolvedShowEndpoint() { return this.modelEndpoints.show || null; },
        dataDb() { return this.checkIfDataIsLoaded ? this.dt.response['data'] : []; },
        errorBag() { return this.dt?.errorBag?.errors || this.dt?.errorBag || null; },
        visibleColumns() { return this.dt.model.getColumns().filter(column => column.visible !== false); },
        selected() { return this.dt.selected; },
        current_page() { return this.checkIfDataIsLoaded ? this.dt.response['meta']['current_page'] : 1; },
        last_page() { return this.checkIfDataIsLoaded ? this.dt.response['meta']['last_page'] : 1; },
        next_page() { return this.checkIfDataIsLoaded ? this.dt.response['meta']['current_page'] + 1 : 1; },
        prev_page() { return this.checkIfDataIsLoaded ? this.dt.response['meta']['current_page'] - 1 : 0; },
        first_page() { return 1; },
        total_pages() { return this.checkIfDataIsLoaded ? this.dt.response['meta']['last_page'] : 1; },
        total_entries() { return this.checkIfDataIsLoaded ? this.dt.response['meta']['total'] : 0; },
        meta_from() { return this.checkIfDataIsLoaded ? this.dt.response['meta']['from'] : 0; },
        meta_to() { return this.checkIfDataIsLoaded ? this.dt.response['meta']['to'] : 0; },
        checkIfDataIsLoaded() { return Array.isArray(this.dt?.response?.data) && this.dt.response.data.length >= 0; },
    },
    methods: {
        setColorPreset(preset) {
            this.colorPreset = preset;
            localStorage.setItem('dt_color_preset', preset);
            this.showThemeMenu = false;
        },
        toggleIconText() {
            this.showIconText = !this.showIconText;
            localStorage.setItem('dt_show_icon_text', this.showIconText);
        },
        getSortClasses(column) {
            if (this.dt.request.getSort !== column.key) return 'text-gray-600 dark:text-gray-400';
            return this.presetClasses.textPrimary + ' font-semibold';
        },
        handlePageInput(e) {
            const page = parseInt(e.target.value);
            if (page > 0 && page <= this.total_pages) {
                this.dt.gotoPage(page);
            } else {
                e.target.value = this.current_page;
            }
        },
        // ... other methods remain similar to original
        getNestedValue(obj, path) { return path.split('.').reduce((acc, part) => acc && acc[part], obj); },
        showAddDialogFunc() { this.showModal = true; this.showAddDialog = true; },
        showDeleteDialogFunc(id) { this.showModal = true; this.showDeleteDialog = true; this.toDeleteId = id; },
        showDeleteSelectedDialogFunc() { this.showModal = true; this.showDeleteSelectedDialog = true; },
        closeDialog() {
            this.showModal = false; this.showDeleteDialog = false; this.showAddDialog = false;
            this.showImportModal = false; this.showDeleteSelectedDialog = false;
            this.dt.closeAllModal = false; this.dt.errorBag = null; this.toDeleteId = null;
        },
        async confirmSingleDelete() {
            if (!this.toDeleteId) return;

            try {
                await this.dt.delete(this.toDeleteId);
                this.closeDialog();
            } catch (error) {
                // Keep modal open so user can review the error state.
            }
        },
        async confirmBulkDelete() {
            if (!this.dt.selected?.length) return;

            const confirmed = window.confirm(
                `Delete ${this.dt.selected.length} selected records? This action cannot be undone.`
            );

            if (!confirmed) {
                return;
            }

            try {
                await this.dt.deleteSelected();
                this.closeDialog();
            } catch (error) {
                // Keep modal open so user can review the error state.
            }
        },
        async initializeDatatable() {
            const requireEndpoint = (endpoint, action) => { if (!endpoint) throw new Error(`Missing ${action} endpoint`); return endpoint; };
            const apiAdapter = {
                get: (params, model) => this.fetchGetApi(requireEndpoint(this.resolvedIndexEndpoint, 'index'), params, model),
                post: (payload) => this.fetchPostApi(requireEndpoint(this.resolvedPostEndpoint, 'post'), payload),
                put: (payload) => {
                    const putEndpoint = requireEndpoint(this.resolvedPutEndpoint, 'put');
                    if (!payload || typeof payload !== 'object') return this.fetchPutApi(putEndpoint, null, payload);
                    const idKey = Object.keys(payload).find((key) => key === 'id' || key.endsWith('_id'));
                    return this.fetchPutApi(putEndpoint, idKey ? payload[idKey] : null, payload);
                },
                delete: (id) => this.fetchDeleteApi(requireEndpoint(this.resolvedDeleteEndpoint, 'delete'), id),
                deleteMany: (ids) => {
                    const deleteManyEndpoint = this.resolvedDeleteManyEndpoint;
                    if (deleteManyEndpoint) {
                        return this.fetchDeleteApi(deleteManyEndpoint, null, { ids });
                    }

                    const deleteEndpoint = requireEndpoint(this.resolvedDeleteEndpoint, 'delete');
                    return Promise.all((ids || []).map((id) => this.fetchDeleteApi(deleteEndpoint, id)));
                },
            };
            this.dt = new CRCMDatatable(this.params, this.baseModel, apiAdapter);
            await this.dt.init();
        },
        onColumnSort(column) {
            if (!column.sortable) return false;
            this.clickSortCtr = (this.clickSortCtr + 1) % 3;
            if (this.clickSortCtr === 0) return false;
            return this.dt.sortFunc({ sort: column.key });
        },
        isRowUpdatable(row) { return this.rowCanUpdate ? !!this.rowCanUpdate(row) : true; },
        isRowDeletable(row) { return this.rowCanDelete ? !!this.rowCanDelete(row) : true; },
        showContextMenu(event, row) {
            this.rowContextMenu = row;
            this.$nextTick(() => {
                const menu = this.$refs.contextMenu;
                if (menu && typeof menu.showMenu === 'function') menu.showMenu(event);
            });
        }
    },
    async mounted() {
        if (this.resolvedIndexEndpoint) await this.initializeDatatable();
        // Close theme menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.group')) this.showThemeMenu = false;
        });
    },
    setup() { return { CRCMDatatable, router }; }
};
</script>

<style scoped>
/* Custom scrollbar for webkit */
.scrollbar-thin::-webkit-scrollbar {
    height: 6px;
    width: 6px;
}

.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-thin::-webkit-scrollbar-thumb {
    background-color: rgb(156 163 175);
    border-radius: 20px;
}

.dark .scrollbar-thin::-webkit-scrollbar-thumb {
    background-color: rgb(75 85 99);
}

/* Smooth transitions */
tr {
    transition: background-color 0.15s ease;
}

button {
    transition: all 0.2s ease;
}

/* Number input spinner hide */
input[type=number]::-webkit-inner-spin-button,
input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>