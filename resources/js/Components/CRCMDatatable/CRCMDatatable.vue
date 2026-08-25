<template>
    <div
        v-if="!resolvedIndexEndpoint"
        class="rounded-lg border border-red-200 bg-red-50 p-4 text-center text-red-600">
        <alert-circle-icon class="mx-auto mb-2 h-12 w-12 opacity-50" />
        <p class="font-medium">Configuration Error</p>
        <p class="text-sm">Unable to retrieve data. Please check model endpoints.</p>
    </div>

    <div
        v-else-if="!canView"
        class="p-8 text-center text-gray-500">
        <shield-icon class="mx-auto mb-3 h-16 w-16 opacity-30" />
        <p class="text-lg font-medium">Access Denied</p>
        <p class="text-sm">You don't have permission to view this data.</p>
    </div>

    <div
        v-else-if="dt instanceof CRCMDatatable"
        id="dtContainer"
        :class="['flex flex-col gap-3 overflow-visible p-2 transition-colors duration-300 sm:p-4', presetClasses.container]">
        <!-- Top Bar: Filters & Actions -->
        <div class="relative z-30 flex flex-col items-start justify-between gap-3 rounded-2xl border border-gray-200 bg-white p-3 shadow-sm sm:p-4 lg:flex-row lg:items-center dark:border-slate-800 dark:bg-slate-900">
            <!-- Left: Filters Section -->
            <div class="flex w-full flex-col flex-wrap items-start gap-2 sm:flex-row sm:items-center lg:w-auto">
                <!-- Default Filters -->
                <div class="flex w-full flex-col items-stretch gap-2 sm:flex-row sm:items-end">
                    <!-- PerPage & SearchBy paired side-by-side on mobile -->
                    <div class="grid w-full grid-cols-2 gap-2 sm:flex sm:w-auto">
                        <per-page
                            :value="dt.request.getPerPage"
                            @changePerPage="dt.perPageFunc({ per_page: $event })"
                            :theme="colorPreset"
                            class="w-full" />

                        <search-by
                            :value="dt.request.getFilter"
                            :is-exact="dt.request.getIsExact"
                            :options="dt.columns"
                            @isExact="dt.isExactFilter({ is_exact: $event })"
                            @searchBy="dt.filterByColumn({ column: $event })"
                            :theme="colorPreset"
                            class="w-full" />
                    </div>

                    <search-filter
                        :model-value="dt.request.getSearch"
                        @searchString="dt.searchFunc({ search: $event })"
                        class="w-full sm:flex-1"
                        :theme="colorPreset" />

                    <scope-filter
                        v-if="showScopeFilter"
                        :value="dt.request.getScope"
                        @change-scope-filter="dt.scopeBy({ scope_by: $event })"
                        :theme="colorPreset"
                        class="w-full sm:w-auto" />
                </div>

                <!-- Custom Filters Slot -->
                <div class="flex flex-wrap items-center gap-2">
                    <slot
                        name="custom-filters"
                        :datatable="dt"
                        :customFilters="dt.request"
                        :refresh="() => dt.refresh()" />
                </div>
            </div>

            <!-- Right: Actions Section -->
            <action-container class="w-full flex-wrap gap-1.5 sm:flex-nowrap lg:w-auto">
                <!-- Theme Selector -->
                <div class="group relative">
                    <top-action-btn
                        @click="showThemeMenu = !showThemeMenu"
                        :class="presetClasses.secondaryBtn"
                        title="Change Theme">
                        <template #icon>
                            <palette-icon class="w-4.5 h-4.5 sm:h-5 sm:w-5" />
                        </template>
                    </top-action-btn>

                    <!-- Theme Dropdown -->
                    <transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 scale-95"
                        enter-to-class="opacity-100 scale-100">
                        <div
                            v-if="showThemeMenu"
                            class="absolute right-0 z-[80] mt-2 w-40 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl dark:border-slate-800 dark:bg-slate-900">
                            <button
                                v-for="(preset, key) in colorPresets"
                                :key="key"
                                @click="setColorPreset(key)"
                                class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm hover:bg-slate-100 dark:hover:bg-slate-800"
                                :class="{
                                    'bg-slate-50 dark:bg-slate-800/60': colorPreset === key,
                                }">
                                <div
                                    class="h-3 w-3 rounded-full"
                                    :class="preset.indicator"></div>
                                <span class="capitalize">{{ key }}</span>
                            </button>
                        </div>
                    </transition>
                </div>

                <!-- Custom Actions Slot -->
                <slot
                    name="custom-actions"
                    :datatable="dt"
                    :selected="dt.selected"
                    :processing="dt.processing" />

                <!-- Standard Actions -->
                <top-action-btn
                    v-if="showActionBtns && canCreate"
                    @click="handleCreateAction()"
                    :class="presetClasses.primaryBtn"
                    title="Add new record">
                    <template #icon>
                        <plus-icon class="w-4.5 h-4.5 sm:h-5 sm:w-5" />
                    </template>
                </top-action-btn>

                <top-action-btn
                    @click="dt.refresh()"
                    :class="dt.processing ? 'cursor-not-allowed opacity-75' : presetClasses.secondaryBtn"
                    :disabled="dt.processing"
                    title="Refresh data">
                    <template #icon>
                        <refresh-cw-icon
                            class="w-4.5 h-4.5 sm:h-5 sm:w-5"
                            :class="{ 'animate-spin': dt.processing }" />
                    </template>
                </top-action-btn>

                <top-action-btn
                    v-if="canDelete && dataDb.length && dt.selected.length && showActionBtns"
                    @click="showDeleteSelectedDialogFunc()"
                    class="bg-red-600 text-white shadow-red-200 hover:bg-red-700"
                    :title="`Delete selected (${dt.selected.length})`">
                    <template #icon>
                        <trash-2-icon class="w-4.5 h-4.5 sm:h-5 sm:w-5" />
                    </template>
                </top-action-btn>

                <top-action-btn
                    v-if="dataDb.length && showActionBtns"
                    :class="presetClasses.ghostBtn"
                    @click="dt.selectAll()"
                    :top-text="dt.selected.length || null"
                    title="Select all visible">
                    <template #icon>
                        <check-square-icon class="w-4.5 h-4.5 sm:h-5 sm:w-5" />
                    </template>
                </top-action-btn>

                <top-action-btn
                    v-if="selected.length && dataDb.length && showActionBtns"
                    class="text-slate-600 transition-colors hover:bg-red-50 dark:text-slate-300 dark:hover:bg-red-950/40"
                    @click="dt.deselectAll()"
                    title="Clear selection">
                    <template #icon>
                        <square-x-icon class="w-4.5 h-4.5 text-red-500 sm:h-5 sm:w-5 dark:text-red-400" />
                    </template>
                </top-action-btn>

                <top-action-btn
                    v-if="dataDb.length && showActionBtns && canView"
                    :class="presetClasses.secondaryBtn"
                    @click="dt.exportCSV()"
                    title="Export CSV">
                    <template #icon>
                        <file-down-icon class="w-4.5 h-4.5 sm:h-5 sm:w-5" />
                    </template>
                </top-action-btn>

                <top-action-btn
                    v-if="showActionBtns && canCreate"
                    :class="presetClasses.secondaryBtn"
                    @click="showImportModal = true"
                    title="Import CSV">
                    <template #icon>
                        <upload-icon class="w-4.5 h-4.5 sm:h-5 sm:w-5" />
                    </template>
                </top-action-btn>
            </action-container>
        </div>

        <!-- Table Container -->
        <div
            id="dtTableContainer"
            class="relative z-10 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div
                v-if="actionWarnings.length"
                class="border-b border-amber-200 bg-amber-50 px-4 py-3 text-amber-900">
                <p class="text-sm font-semibold">Action configuration warning</p>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    <li
                        v-for="warning in actionWarnings"
                        :key="warning">
                        {{ warning }}
                    </li>
                </ul>
            </div>

            <!-- Loading Overlay -->
            <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0">
                <div
                    v-if="dt.processing"
                    class="absolute inset-0 z-40 flex flex-col items-center justify-center gap-3 bg-white/90 dark:bg-slate-900/90">
                    <loader-2-icon
                        class="h-10 w-10 animate-spin"
                        :class="presetClasses.textPrimary" />
                    <span class="text-sm font-medium text-slate-600 dark:text-slate-400">Loading data...</span>
                </div>
            </transition>

            <div class="scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-slate-700 z-10 overflow-x-auto">
                <table
                    id="dtTable"
                    class="w-full text-left text-sm">
                    <crcm-thead :class="presetClasses.headerBg">
                        <thead-row>
                            <th class="w-10 p-3 text-center">
                                <span class="sr-only">Select</span>
                            </th>
                            <th
                                v-for="column in dt.model.getColumns()"
                                :key="column.key + column.title"
                                class="cursor-pointer select-none whitespace-nowrap p-3 text-xs font-semibold uppercase tracking-wider transition-colors"
                                :class="[column.sortable ? 'hover:bg-black/5 dark:hover:bg-white/5' : '', column.visible !== false ? '' : 'hidden', getSortClasses(column)]"
                                @click="onColumnSort(column)">
                                <div
                                    class="flex items-center gap-1.5"
                                    :class="column.align ? column.align : 'text-left'">
                                    <span>{{ column.title }}</span>
                                    <span
                                        v-if="column.sortable"
                                        class="text-[10px] opacity-50">
                                        <arrow-up-icon
                                            v-if="dt.request.getSort === column.key && dt.request.getParam('order') === 'asc'"
                                            class="h-3 w-3" />
                                        <arrow-down-icon
                                            v-else-if="dt.request.getSort === column.key && dt.request.getParam('order') === 'desc'"
                                            class="h-3 w-3" />
                                        <more-horizontal-icon
                                            v-else
                                            class="h-3 w-3 opacity-0 group-hover:opacity-50" />
                                    </span>
                                </div>
                            </th>
                            <th
                                v-if="showActionBtns"
                                class="p-3 text-right text-xs uppercase tracking-wider">
                                Actions
                            </th>
                        </thead-row>
                    </crcm-thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-slate-800">
                        <template v-if="!dt.processing">
                            <tr v-if="dataDb.length === 0">
                                <td
                                    :colspan="dt.model.getColumns().length + 2"
                                    class="p-8 text-center text-slate-500">
                                    <div class="flex flex-col items-center gap-2">
                                        <search-x-icon class="h-12 w-12 opacity-20" />
                                        <p class="font-medium">No records found</p>
                                        <p class="text-xs">Try adjusting your filters or search terms</p>
                                    </div>
                                </td>
                            </tr>

                            <tr
                                v-for="row in dataDb"
                                :key="row.id"
                                class="group border-b border-gray-100 transition-colors last:border-0 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/50"
                                :class="[dt.isSelected(row.id) ? presetClasses.selectedRow : '']"
                                @contextmenu.prevent="showContextMenu($event, row)">
                                <!-- Selection Cell -->
                                <td class="p-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <span class="w-6 text-right font-mono text-xs text-slate-400">
                                            {{ meta_from + dataDb.indexOf(row) }}
                                        </span>
                                        <input
                                            type="checkbox"
                                            :checked="dt.isSelected(row.id)"
                                            :disabled="!isRowDeletable(row)"
                                            @click.stop="dt.addSelected(row.id)"
                                            class="h-4 w-4 rounded border-slate-300 text-current transition-all focus:ring-2 focus:ring-offset-0 disabled:opacity-50 dark:border-slate-700"
                                            :class="presetClasses.checkbox" />
                                    </div>
                                </td>

                                <!-- Data Cells -->
                                <td
                                    v-for="column in visibleColumns"
                                    :key="column.key"
                                    class="max-w-xs truncate p-3 text-slate-700 dark:text-slate-300"
                                    :class="[column.align || 'text-left', column.visible === false ? 'hidden' : '']"
                                    @dblclick="dt.addSelected(row.id)"
                                    @click.ctrl="dt.addSelected(row.id)">
                                    <slot
                                        :name="`cell-${column.key}`"
                                        :row="row"
                                        :value="getNestedValue(row, column.key)">
                                        {{ getNestedValue(row, column.key) }}
                                    </slot>
                                </td>

                                <!-- Actions Cell -->
                                <td
                                    v-if="showActionBtns"
                                    class="p-3 text-right">
                                    <div class="flex items-center justify-end gap-1 opacity-100 transition-opacity sm:opacity-0 sm:group-hover:opacity-100">
                                        <slot
                                            name="rowActions"
                                            :row="row" />

                                        <button
                                            v-if="canView && resolveRowShowEndpoint(row)"
                                            @click="visitRowEndpoint(row, 'show')"
                                            class="rounded-lg p-1.5 text-blue-600 transition-colors hover:bg-blue-100 dark:text-blue-400 dark:hover:bg-blue-900/30"
                                            title="View">
                                            <eye-icon class="sm:w-4.5 sm:h-4.5 h-4 w-4" />
                                        </button>

                                        <button
                                            v-if="canUpdate && isRowUpdatable(row) && resolveRowUpdateEndpoint(row)"
                                            @click="visitRowEndpoint(row, 'update')"
                                            class="rounded-lg p-1.5 text-amber-600 transition-colors hover:bg-amber-100 dark:text-amber-400 dark:hover:bg-amber-900/30"
                                            title="Edit">
                                            <file-edit-icon class="sm:w-4.5 sm:h-4.5 h-4 w-4" />
                                        </button>

                                        <button
                                            v-if="canDelete && isRowDeletable(row)"
                                            @click="showDeleteDialogFunc(row.id)"
                                            class="rounded-lg p-1.5 text-red-600 transition-colors hover:bg-red-100 dark:text-red-400 dark:hover:bg-red-900/30"
                                            title="Delete">
                                            <trash-2-icon class="sm:w-4.5 sm:h-4.5 h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Footer Info -->
            <div class="flex flex-col items-center justify-between gap-2 border-t border-gray-200 bg-slate-50/50 p-3 text-xs text-slate-600 sm:flex-row sm:p-4 dark:border-slate-800 dark:bg-slate-900/50 dark:text-slate-400">
                <div class="flex items-center gap-2">
                    <span>
                        Showing
                        <strong>{{ meta_from }}-{{ meta_to }}</strong>
                        of
                        <strong>{{ total_entries }}</strong>
                    </span>
                    <span
                        v-if="dt.selected.length"
                        class="rounded-full px-2 py-0.5 text-[10px] font-medium text-white"
                        :class="presetClasses.badge">
                        {{ dt.selected.length }} selected
                    </span>
                </div>

                <!-- Mobile Pagination -->
                <div class="flex items-center gap-1 sm:hidden">
                    <button
                        @click="dt.prevPage()"
                        :disabled="!prev_page"
                        class="rounded-lg p-2 disabled:opacity-50"
                        :class="presetClasses.ghostBtn">
                        <chevron-left-icon class="h-5 w-5" />
                    </button>
                    <span class="px-3 py-1 text-sm font-medium">{{ current_page }} / {{ total_pages }}</span>
                    <button
                        @click="dt.nextPage()"
                        :disabled="current_page === last_page"
                        class="rounded-lg p-2 disabled:opacity-50"
                        :class="presetClasses.ghostBtn">
                        <chevron-right-icon class="h-5 w-5" />
                    </button>
                </div>

                <!-- Desktop Pagination -->
                <div class="hidden items-center gap-1 sm:flex">
                    <button
                        @click="dt.firstPage()"
                        :disabled="current_page === first_page"
                        class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                        :class="presetClasses.secondaryBtn">
                        First
                    </button>
                    <button
                        @click="dt.prevPage()"
                        :disabled="!prev_page"
                        class="flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                        :class="presetClasses.secondaryBtn">
                        <chevron-left-icon class="h-3 w-3" />
                        Prev
                    </button>

                    <div class="flex items-center gap-1 px-2">
                        <input
                            ref="pageInput"
                            type="number"
                            :value="current_page"
                            min="1"
                            :max="total_pages"
                            @keydown.enter="handlePageInput"
                            class="w-12 rounded-md border bg-transparent px-2 py-1 text-center text-xs focus:border-transparent focus:ring-2"
                            :class="presetClasses.input" />
                        <span class="text-slate-400">/</span>
                        <span class="text-xs font-medium">{{ total_pages }}</span>
                    </div>

                    <button
                        @click="dt.nextPage()"
                        :disabled="current_page === last_page"
                        class="flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                        :class="presetClasses.secondaryBtn">
                        Next
                        <chevron-right-icon class="h-3 w-3" />
                    </button>
                    <button
                        @click="dt.lastPage()"
                        :disabled="current_page === last_page"
                        class="rounded-lg px-3 py-1.5 text-xs font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                        :class="presetClasses.secondaryBtn">
                        Last
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Floating Selection Pill -->
        <transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-8"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-8">
            <div
                v-if="dt.selected.length && showActionBtns"
                class="fixed bottom-4 left-4 right-4 z-40 flex items-center justify-between gap-2 rounded-2xl border border-slate-800 bg-slate-900/95 p-2.5 text-white shadow-2xl sm:hidden">
                <span class="px-2 text-xs font-semibold">{{ dt.selected.length }} selected</span>
                <div class="flex items-center gap-1.5">
                    <button
                        v-if="canDelete"
                        @click="showDeleteSelectedDialogFunc()"
                        class="rounded-xl bg-red-600 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-red-700">
                        Delete
                    </button>
                    <button
                        @click="dt.deselectAll()"
                        class="rounded-xl bg-slate-800 px-2.5 py-1.5 text-xs font-semibold text-slate-300 transition-colors hover:bg-slate-700">
                        Clear
                    </button>
                </div>
            </div>
        </transition>

        <!-- Context Menu -->
        <context-menu
            ref="contextMenu"
            v-if="rowContextMenu"
            @close="rowContextMenu = null">
            <div class="min-w-[160px] rounded-xl border border-gray-200 bg-white py-1 shadow-xl dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-1 border-b border-gray-100 px-3 py-2 text-xs font-semibold text-slate-500 dark:border-slate-800">Actions</div>
                <slot
                    name="rowActionsMenu"
                    :row="rowContextMenu" />

                <button
                    v-if="canView && resolveRowShowEndpoint(rowContextMenu)"
                    @click="visitRowEndpoint(rowContextMenu, 'show')"
                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                    <eye-icon class="h-4 w-4 text-blue-500" />
                    View Details
                </button>

                <button
                    v-if="canUpdate && isRowUpdatable(rowContextMenu) && resolveRowUpdateEndpoint(rowContextMenu)"
                    @click="visitRowEndpoint(rowContextMenu, 'update')"
                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800">
                    <file-edit-icon class="h-4 w-4 text-amber-500" />
                    Edit Record
                </button>

                <div
                    v-if="canDelete && isRowDeletable(rowContextMenu)"
                    class="mt-1 border-t border-gray-100 pt-1 dark:border-slate-800">
                    <button
                        @click="
                            showDeleteDialogFunc(rowContextMenu.id);
                            rowContextMenu = null;
                        "
                        class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                        <trash-2-icon class="h-4 w-4" />
                        Delete
                    </button>
                </div>
            </div>
        </context-menu>

        <!-- Modals remain similar but styled with presets -->
        <dialog-form-modal
            :show="showImportModal && canCreate"
            @close="closeDialog">
            <component
                :is="importModal"
                v-if="importModal"
                :processing="dt.processing"
                :errors="errorBag"
                @uploadForm="dt.importCSV($event)"
                @close="closeDialog"
                :forceClose="dt.closeAllModal"
                :theme="colorPreset" />
        </dialog-form-modal>

        <dialog-form-modal
            :show="showAddDialog && canCreate"
            @close="closeDialog">
            <component
                :is="addForm"
                v-if="addForm"
                :processing="dt.processing"
                :errors="errorBag"
                @submitForm="dt.create($event)"
                @close="closeDialog"
                :forceClose="dt.closeAllModal"
                :theme="colorPreset" />
        </dialog-form-modal>

        <!-- Delete Confirmation -->
        <dialog-modal
            :show="showDeleteDialog && canDelete"
            @close="closeDialog">
            <template #title>
                <div class="flex items-center gap-2 text-red-600">
                    <alert-triangle-icon class="h-5 w-5" />
                    Confirm Deletion
                </div>
            </template>
            <template #content>
                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                    <p>Are you sure you want to delete this record?</p>
                    <div
                        class="rounded-lg bg-gray-100 p-3 font-mono text-xs dark:bg-gray-800"
                        v-if="toDeleteId">
                        ID: {{ toDeleteId }}
                    </div>
                    <p class="text-xs text-red-500">This action cannot be undone.</p>
                </div>
            </template>
            <template #footer>
                <div class="flex w-full justify-between">
                    <button
                        @click="confirmSingleDelete"
                        :disabled="dt.processing"
                        class="flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50">
                        <trash-2-icon
                            v-if="!dt.processing"
                            class="h-4 w-4" />
                        <loader-2-icon
                            v-else
                            class="h-4 w-4 animate-spin" />
                        Delete
                    </button>
                    <button
                        @click="closeDialog"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                </div>
            </template>
        </dialog-modal>

        <!-- Bulk Delete Confirmation -->
        <dialog-modal
            :show="showDeleteSelectedDialog && canDelete"
            @close="closeDialog">
            <template #title>
                <div class="flex items-center gap-2 text-red-600">
                    <alert-triangle-icon class="h-5 w-5" />
                    Delete Multiple Records
                </div>
            </template>
            <template #content>
                <div class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <p>
                        You are about to delete
                        <strong>{{ dt.selected.length }}</strong>
                        records:
                    </p>
                    <div class="max-h-32 space-y-1 overflow-y-auto rounded bg-gray-100 p-2 font-mono text-xs dark:bg-gray-800">
                        <div
                            v-for="id in dt.selected.slice(0, 10)"
                            :key="id"
                            class="text-gray-600 dark:text-gray-400">
                            ID: {{ id }}
                        </div>
                        <div
                            v-if="dt.selected.length > 10"
                            class="italic text-gray-400">
                            ... and {{ dt.selected.length - 10 }} more
                        </div>
                    </div>
                    <p class="text-xs text-red-500">This action cannot be undone.</p>
                </div>
            </template>
            <template #footer>
                <button
                    @click="closeDialog"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    Cancel
                </button>
                <button
                    @click="confirmBulkDelete"
                    :disabled="dt.processing"
                    class="flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50">
                    <trash-2-icon
                        v-if="!dt.processing"
                        class="h-4 w-4" />
                    <loader-2-icon
                        v-else
                        class="h-4 w-4 animate-spin" />
                    Delete All
                </button>
            </template>
        </dialog-modal>
    </div>
</template>

<script setup>
// Lucide Icons - assuming globally registered or import specific ones
import { Plus, RefreshCw, Trash2, CheckSquare, Square, SquareX, FileDown, Upload, Eye, FileEdit, ChevronLeft, ChevronRight, Loader2, Search, Filter, Palette, Type, ToggleRight, ArrowUp, ArrowDown, MoreHorizontal, AlertCircle, AlertTriangle, Shield, SearchX } from "lucide-vue-next";

import ActionContainer from "@/Components/CRCMDatatable/Layouts/ActionContainer.vue";
import DialogFormModal from "@/Components/CRCMDatatable/Layouts/DialogFormModal.vue";

// Component imports remain similar
import { ContextMenu, CrcmTable, CrcmTbody, CrcmThead, TheadRow, TbodyRow } from "@/Components/CRCMDatatable/Components";

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
import { resolveDatatableRealtimeSubscriptions, subscribeToRealtimeChannels } from "@/Modules/realtime/subscriptions";

// Icon mapping for template usage
const icons = {
    PlusIcon: Plus,
    RefreshCwIcon: RefreshCw,
    Trash2Icon: Trash2,
    CheckSquareIcon: CheckSquare,
    SquareIcon: Square,
    SquareXIcon: SquareX,
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
    SearchXIcon: SearchX,
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
        defaultColorPreset: { type: String, default: "lime" },
        storageKey: { type: String, default: null },
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
            colorPreset: localStorage.getItem("dt_color_preset") || this.defaultColorPreset,
            clickSortCtr: 0,
            realtimeCleanup: null,
            realtimeRefreshTimer: null,
            themeMenuClickHandler: null,
            preservedScrollState: null,
        };
    },
    computed: {
        colorPresets() {
            return {
                lime: {
                    indicator: "bg-lime-500",
                    primaryBtn: "bg-lime-600 hover:bg-lime-700 text-white shadow-sm",
                    secondaryBtn: "bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200",
                    ghostBtn: "hover:bg-slate-100 text-slate-600 dark:hover:bg-slate-800 dark:text-slate-300",
                    headerBg: "bg-lime-50 dark:bg-lime-950/40 text-lime-950 dark:text-lime-200",
                    selectedRow: "bg-lime-50/70 dark:bg-lime-950/30",
                    textPrimary: "text-lime-600 dark:text-lime-400",
                    checkbox: "text-lime-600 focus:ring-lime-500",
                    badge: "bg-lime-600",
                    input: "border-slate-300 dark:border-slate-700 focus:ring-lime-500",
                    container: "",
                },
                emerald: {
                    indicator: "bg-emerald-500",
                    primaryBtn: "bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm",
                    secondaryBtn: "bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200",
                    ghostBtn: "hover:bg-slate-100 text-slate-600 dark:hover:bg-slate-800 dark:text-slate-300",
                    headerBg: "bg-emerald-50 dark:bg-emerald-950/40 text-emerald-950 dark:text-emerald-200",
                    selectedRow: "bg-emerald-50 dark:bg-emerald-950/30",
                    textPrimary: "text-emerald-600 dark:text-emerald-400",
                    checkbox: "text-emerald-600 focus:ring-emerald-500",
                    badge: "bg-emerald-600",
                    input: "border-slate-300 dark:border-slate-700 focus:ring-emerald-500",
                    container: "",
                },
                blue: {
                    indicator: "bg-blue-500",
                    primaryBtn: "bg-blue-600 hover:bg-blue-700 text-white shadow-sm",
                    secondaryBtn: "bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200",
                    ghostBtn: "hover:bg-slate-100 text-slate-600 dark:hover:bg-slate-800 dark:text-slate-300",
                    headerBg: "bg-blue-50 dark:bg-blue-950/40 text-blue-950 dark:text-blue-200",
                    selectedRow: "bg-blue-50 dark:bg-blue-950/30",
                    textPrimary: "text-blue-600 dark:text-blue-400",
                    checkbox: "text-blue-600 focus:ring-blue-500",
                    badge: "bg-blue-600",
                    input: "border-slate-300 dark:border-slate-700 focus:ring-blue-500",
                    container: "",
                },
                purple: {
                    indicator: "bg-purple-500",
                    primaryBtn: "bg-purple-600 hover:bg-purple-700 text-white shadow-sm",
                    secondaryBtn: "bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200",
                    ghostBtn: "hover:bg-slate-100 text-slate-600 dark:hover:bg-slate-800 dark:text-slate-300",
                    headerBg: "bg-purple-50 dark:bg-purple-950/40 text-purple-950 dark:text-purple-200",
                    selectedRow: "bg-purple-50 dark:bg-purple-950/30",
                    textPrimary: "text-purple-600 dark:text-purple-400",
                    checkbox: "text-purple-600 focus:ring-purple-500",
                    badge: "bg-purple-600",
                    input: "border-slate-300 dark:border-slate-700 focus:ring-purple-500",
                    container: "",
                },
                orange: {
                    indicator: "bg-orange-500",
                    primaryBtn: "bg-orange-600 hover:bg-orange-700 text-white shadow-sm",
                    secondaryBtn: "bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200",
                    ghostBtn: "hover:bg-slate-100 text-slate-600 dark:hover:bg-slate-800 dark:text-slate-300",
                    headerBg: "bg-orange-50 dark:bg-orange-950/40 text-orange-950 dark:text-orange-200",
                    selectedRow: "bg-orange-50 dark:bg-orange-950/30",
                    textPrimary: "text-orange-600 dark:text-orange-400",
                    checkbox: "text-orange-600 focus:ring-orange-500",
                    badge: "bg-orange-600",
                    input: "border-slate-300 dark:border-slate-700 focus:ring-orange-500",
                    container: "",
                },
                slate: {
                    indicator: "bg-slate-700",
                    primaryBtn: "bg-slate-800 hover:bg-slate-900 text-white shadow-sm",
                    secondaryBtn: "bg-slate-200 hover:bg-slate-300 text-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200",
                    ghostBtn: "hover:bg-slate-200 text-slate-700 dark:hover:bg-slate-800 dark:text-slate-300",
                    headerBg: "bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100",
                    selectedRow: "bg-slate-100 dark:bg-slate-800/60",
                    textPrimary: "text-slate-800 dark:text-slate-200",
                    checkbox: "text-slate-800 focus:ring-slate-700",
                    badge: "bg-slate-800",
                    input: "border-slate-300 dark:border-slate-700 focus:ring-slate-700",
                    container: "",
                },
            };
        },
        presetClasses() {
            return this.colorPresets[this.colorPreset] || this.colorPresets.lime;
        },
        // ... other computed properties remain the same as original
        isAuthenticated() {
            return !!this.$page?.props?.auth?.user;
        },
        modelEndpoints() {
            return this.baseModel?.endpoints || {};
        },
        resolvedIndexEndpoint() {
            if (this.isAuthenticated) return this.modelEndpoints.indexAuth || this.modelEndpoints.index || this.modelEndpoints.indexGuest || null;
            return this.modelEndpoints.indexGuest || this.modelEndpoints.index || this.modelEndpoints.indexAuth || null;
        },
        resolvedPostEndpoint() {
            if (this.isAuthenticated) return this.modelEndpoints.postAuth || this.modelEndpoints.post || this.modelEndpoints.postGuest || null;
            return this.modelEndpoints.postGuest || this.modelEndpoints.post || this.modelEndpoints.postAuth || null;
        },
        resolvedCreateEndpoint() {
            return this.modelEndpoints.create || this.baseModel?.createPage || null;
        },
        resolvedPutEndpoint() {
            return this.modelEndpoints.put || null;
        },
        resolvedDeleteEndpoint() {
            return this.modelEndpoints.delete || null;
        },
        resolvedDeleteManyEndpoint() {
            return this.modelEndpoints.deleteMany || this.modelEndpoints.multiDestroy || (this.resolvedDeleteEndpoint ? this.resolvedDeleteEndpoint.replace(".destroy", ".multi-destroy") : null);
        },
        resolvedShowEndpoint() {
            return this.modelEndpoints.show || null;
        },
        actionWarnings() {
            const warnings = [];

            if (this.canCreate) {
                if (!this.resolvedCreateEndpoint && !this.addForm) {
                    warnings.push("Create is enabled, but no create page endpoint or add-form component is defined for this model.");
                }

                if (this.addForm && !this.resolvedPostEndpoint) {
                    warnings.push("Create modal is enabled, but the create API endpoint is not defined for this model.");
                }
            }

            if (this.canUpdate && !this.resolvedShowEndpoint) {
                warnings.push("Update is enabled, but the show page endpoint is not defined for this model.");
            }

            if (this.canDelete && !this.resolvedDeleteEndpoint) {
                warnings.push("Delete is enabled, but the delete API endpoint is not defined for this model.");
            }

            return warnings;
        },
        dataDb() {
            return this.checkIfDataIsLoaded ? this.dt.response["data"] : [];
        },
        errorBag() {
            return this.dt?.errorBag?.errors || this.dt?.errorBag || null;
        },
        visibleColumns() {
            return this.dt.model.getColumns().filter((column) => column.visible !== false);
        },
        selected() {
            return this.dt.selected;
        },
        current_page() {
            return this.checkIfDataIsLoaded ? this.dt.response["meta"]["current_page"] : 1;
        },
        last_page() {
            return this.checkIfDataIsLoaded ? this.dt.response["meta"]["last_page"] : 1;
        },
        next_page() {
            return this.checkIfDataIsLoaded ? this.dt.response["meta"]["current_page"] + 1 : 1;
        },
        prev_page() {
            return this.checkIfDataIsLoaded ? this.dt.response["meta"]["current_page"] - 1 : 0;
        },
        first_page() {
            return 1;
        },
        total_pages() {
            return this.checkIfDataIsLoaded ? this.dt.response["meta"]["last_page"] : 1;
        },
        total_entries() {
            return this.checkIfDataIsLoaded ? this.dt.response["meta"]["total"] : 0;
        },
        meta_from() {
            return this.checkIfDataIsLoaded ? this.dt.response["meta"]["from"] : 0;
        },
        meta_to() {
            return this.checkIfDataIsLoaded ? this.dt.response["meta"]["to"] : 0;
        },
        checkIfDataIsLoaded() {
            return Array.isArray(this.dt?.response?.data) && this.dt.response.data.length >= 0;
        },
    },
    methods: {
        setColorPreset(preset) {
            this.colorPreset = preset;
            localStorage.setItem("dt_color_preset", preset);
            this.showThemeMenu = false;
        },
        toggleIconText() {
            this.showIconText = !this.showIconText;
            localStorage.setItem("dt_show_icon_text", this.showIconText);
        },
        getSortClasses(column) {
            if (this.dt.request.getSort !== column.key) return "text-gray-600 dark:text-gray-400";
            return this.presetClasses.textPrimary + " font-semibold";
        },
        handlePageInput(e) {
            const page = parseInt(e.target.value);
            if (page > 0 && page <= this.total_pages) {
                this.dt.gotoPage(page);
            } else {
                e.target.value = this.current_page;
            }
        },
        resolveRowRouteParams(row, action = "show") {
            const actionParamsKey = action === "update" ? "updatePageParams" : "showPageParams";
            const params = row?.[actionParamsKey];

            if (params !== undefined && params !== null) {
                return params;
            }

            return row?.id ? { id: row.id } : null;
        },
        resolveRowShowEndpoint(row) {
            return row?.showPage || this.resolvedShowEndpoint || null;
        },
        resolveRowUpdateEndpoint(row) {
            return row?.updatePage || row?.showPage || this.resolvedShowEndpoint || null;
        },
        visitRowEndpoint(row, action = "show") {
            const endpoint = action === "update" ? this.resolveRowUpdateEndpoint(row) : this.resolveRowShowEndpoint(row);

            if (!endpoint) {
                this.notifyActionWarning(`Unable to ${action === "update" ? "open edit" : "view"} page. No page endpoint is configured for this row.`);
                return;
            }

            const params = this.resolveRowRouteParams(row, action);
            // @ts-ignore
            const url = params ? route(endpoint, params) : route(endpoint);
            const target = action === "update" ? row?.updatePageTarget || row?.showPageTarget || "_self" : row?.showPageTarget || "_self";

            if (target && target !== "_self") {
                window.open(url, target, "noopener");
                return;
            }

            router.visit(url);
        },
        // ... other methods remain similar to original
        getNestedValue(obj, path) {
            return path.split(".").reduce((acc, part) => acc && acc[part], obj);
        },
        notifyActionWarning(message) {
            if (!message || typeof window === "undefined") {
                return;
            }

            window.dispatchEvent(
                new CustomEvent("cbc:notify", {
                    detail: {
                        type: "warning",
                        message,
                    },
                }),
            );
        },
        getScrollableAncestors() {
            const elements = [];
            let current = this.$el instanceof HTMLElement ? this.$el.parentElement : null;

            while (current) {
                const style = window.getComputedStyle(current);
                const overflowY = style.overflowY;
                const overflowX = style.overflowX;
                const canScrollY = /(auto|scroll|overlay)/.test(overflowY) && current.scrollHeight > current.clientHeight;
                const canScrollX = /(auto|scroll|overlay)/.test(overflowX) && current.scrollWidth > current.clientWidth;

                if (canScrollY || canScrollX) {
                    elements.push(current);
                }

                current = current.parentElement;
            }

            return elements;
        },
        captureScrollState() {
            if (typeof window === "undefined") {
                return null;
            }

            const state = {
                windowX: window.scrollX,
                windowY: window.scrollY,
                ancestors: this.getScrollableAncestors().map((element) => ({
                    element,
                    top: element.scrollTop,
                    left: element.scrollLeft,
                })),
            };

            const tableContainer = this.$el?.querySelector?.("#dtTableContainer");
            if (tableContainer instanceof HTMLElement) {
                state.tableTop = tableContainer.scrollTop;
                state.tableLeft = tableContainer.scrollLeft;
            }

            return state;
        },
        restoreScrollState(state = null) {
            if (!state || typeof window === "undefined") {
                return;
            }

            const applyState = () => {
                window.scrollTo(state.windowX, state.windowY);

                (state.ancestors || []).forEach(({ element, top, left }) => {
                    if (element instanceof HTMLElement) {
                        element.scrollTop = top;
                        element.scrollLeft = left;
                    }
                });

                const tableContainer = this.$el?.querySelector?.("#dtTableContainer");
                if (tableContainer instanceof HTMLElement) {
                    tableContainer.scrollTop = state.tableTop ?? tableContainer.scrollTop;
                    tableContainer.scrollLeft = state.tableLeft ?? tableContainer.scrollLeft;
                }
            };

            this.$nextTick(() => {
                requestAnimationFrame(() => {
                    applyState();
                    requestAnimationFrame(applyState);
                });
            });
        },
        decorateDatatableForScrollRetention() {
            if (!this.dt || this.dt.__scrollRetentionDecorated) {
                return;
            }

            const originalRefresh = this.dt.refresh.bind(this.dt);
            this.dt.refresh = async (...args) => {
                const scrollState = this.captureScrollState();

                try {
                    return await originalRefresh(...args);
                } finally {
                    this.restoreScrollState(scrollState);
                }
            };

            this.dt.__scrollRetentionDecorated = true;
        },
        handleCreateAction() {
            if (this.resolvedCreateEndpoint) {
                router.visit(route(this.resolvedCreateEndpoint));
                return;
            }

            if (this.addForm) {
                this.showModal = true;
                this.showAddDialog = true;
                return;
            }

            this.notifyActionWarning(this.actionWarnings[0] || "Create action is not configured for this model.");
        },
        showAddDialogFunc() {
            this.showModal = true;
            this.showAddDialog = true;
        },
        showDeleteDialogFunc(id) {
            if (!this.resolvedDeleteEndpoint) {
                this.notifyActionWarning("Delete action is enabled, but the delete API endpoint is not defined for this model.");
                return;
            }

            this.showModal = true;
            this.showDeleteDialog = true;
            this.toDeleteId = id;
        },
        showDeleteSelectedDialogFunc() {
            if (!this.resolvedDeleteEndpoint) {
                this.notifyActionWarning("Delete action is enabled, but the delete API endpoint is not defined for this model.");
                return;
            }

            this.showModal = true;
            this.showDeleteSelectedDialog = true;
        },
        closeDialog() {
            this.showModal = false;
            this.showDeleteDialog = false;
            this.showAddDialog = false;
            this.showImportModal = false;
            this.showDeleteSelectedDialog = false;
            this.dt.closeAllModal = false;
            this.dt.errorBag = null;
            this.toDeleteId = null;
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

            const confirmed = window.confirm(`Delete ${this.dt.selected.length} selected records? This action cannot be undone.`);

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
            const requireEndpoint = (endpoint, action) => {
                if (!endpoint) throw new Error(`Missing ${action} endpoint`);
                return endpoint;
            };
            const apiAdapter = {
                get: (params, model) => this.fetchGetApi(requireEndpoint(this.resolvedIndexEndpoint, "index"), params, model),
                post: (payload) => this.fetchPostApi(requireEndpoint(this.resolvedPostEndpoint, "post"), payload),
                put: (payload) => {
                    const putEndpoint = requireEndpoint(this.resolvedPutEndpoint, "put");
                    if (!payload || typeof payload !== "object") return this.fetchPutApi(putEndpoint, null, payload);
                    const idKey = Object.keys(payload).find((key) => key === "id" || key.endsWith("_id"));
                    return this.fetchPutApi(putEndpoint, idKey ? payload[idKey] : null, payload);
                },
                delete: (id) => this.fetchDeleteApi(requireEndpoint(this.resolvedDeleteEndpoint, "delete"), id),
                deleteMany: (ids) => {
                    const deleteManyEndpoint = this.resolvedDeleteManyEndpoint;
                    if (deleteManyEndpoint) {
                        return this.fetchDeleteApi(deleteManyEndpoint, null, { ids });
                    }

                    const deleteEndpoint = requireEndpoint(this.resolvedDeleteEndpoint, "delete");
                    return Promise.all((ids || []).map((id) => this.fetchDeleteApi(deleteEndpoint, id)));
                },
            };
            this.dt = new CRCMDatatable(this.params, this.baseModel, apiAdapter, this.storageKey);
            await this.dt.init();
            this.decorateDatatableForScrollRetention();
        },
        onColumnSort(column) {
            if (!column.sortable) return false;
            this.clickSortCtr = (this.clickSortCtr + 1) % 3;
            if (this.clickSortCtr === 0) return false;
            return this.dt.sortFunc({ sort: column.key });
        },
        isRowUpdatable(row) {
            return this.rowCanUpdate ? !!this.rowCanUpdate(row) : true;
        },
        isRowDeletable(row) {
            return this.rowCanDelete ? !!this.rowCanDelete(row) : true;
        },
        showContextMenu(event, row) {
            this.rowContextMenu = row;
            this.$nextTick(() => {
                const menu = this.$refs.contextMenu;
                if (menu && typeof menu.showMenu === "function") menu.showMenu(event);
            });
        },
        cleanupRealtime() {
            if (typeof this.realtimeCleanup === "function") {
                this.realtimeCleanup();
            }

            this.realtimeCleanup = null;
        },
        scheduleRealtimeRefresh() {
            if (this.realtimeRefreshTimer) {
                clearTimeout(this.realtimeRefreshTimer);
            }

            this.realtimeRefreshTimer = setTimeout(() => {
                if (this.dt && typeof this.dt.refresh === "function") {
                    this.dt.refresh();
                }
            }, 400);
        },
        configureRealtime() {
            this.cleanupRealtime();

            const subscriptions = resolveDatatableRealtimeSubscriptions(this.resolvedIndexEndpoint).map((subscription) => ({
                ...subscription,
                handler: (payload) => {
                    if (typeof subscription.shouldRefresh === "function" && !subscription.shouldRefresh(payload)) {
                        return;
                    }

                    this.scheduleRealtimeRefresh();
                },
            }));

            if (!subscriptions.length) {
                return;
            }

            this.realtimeCleanup = subscribeToRealtimeChannels(subscriptions);
        },
    },
    async mounted() {
        if (this.resolvedIndexEndpoint) await this.initializeDatatable();
        this.configureRealtime();
        // Close theme menu when clicking outside
        this.themeMenuClickHandler = (e) => {
            if (!e.target.closest(".group")) this.showThemeMenu = false;
        };
        document.addEventListener("click", this.themeMenuClickHandler);
    },
    beforeUnmount() {
        if (this.realtimeRefreshTimer) {
            clearTimeout(this.realtimeRefreshTimer);
        }

        this.cleanupRealtime();

        if (this.themeMenuClickHandler) {
            document.removeEventListener("click", this.themeMenuClickHandler);
        }
    },
    setup() {
        return { CRCMDatatable, router };
    },
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
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>
