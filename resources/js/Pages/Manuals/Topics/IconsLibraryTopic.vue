<script>
import * as Icons from '@/Components/Icons'
import TopicLayout from '../Components/TopicLayout.vue'

export default {
    name: 'IconsLibraryTopic',
    components: {
        TopicLayout
    },
    data() {
        return {
            search: '',
            copiedName: '',
        }
    },
    computed: {
        icons() {
            return Object.entries(Icons)
                .map(([name, component]) => ({ name, component }))
                .sort((a, b) => a.name.localeCompare(b.name))
        },
        filteredIcons() {
            const query = this.search.trim().toLowerCase()

            if (!query) {
                return this.icons
            }

            return this.icons.filter((icon) => {
                const kebab = this.toKebabCase(icon.name)
                return icon.name.toLowerCase().includes(query) || kebab.includes(query)
            })
        },
    },
    methods: {
        toKebabCase(value) {
            return value
                .replace(/([A-Z])([A-Z][a-z])/g, '$1-$2')
                .replace(/([a-z0-9])([A-Z])/g, '$1-$2')
                .toLowerCase()
        },
        async copyIconTag(iconName) {
            const tag = `<${this.toKebabCase(iconName)} />`

            if (navigator?.clipboard?.writeText) {
                await navigator.clipboard.writeText(tag)
            } else {
                const textArea = document.createElement('textarea')
                textArea.value = tag
                textArea.style.position = 'fixed'
                textArea.style.opacity = '0'
                document.body.appendChild(textArea)
                textArea.select()
                document.execCommand('copy')
                document.body.removeChild(textArea)
            }

            this.copiedName = iconName
            setTimeout(() => {
                if (this.copiedName === iconName) {
                    this.copiedName = ''
                }
            }, 1200)
        },
    },
}
</script>

<template>
    <TopicLayout
        title="Available Icons"
        description="This list is automatically generated from resources/js/Components/Icons/index.ts. Any new icon exported there appears here automatically."
        icon="LuShapes"
        maxWidth="max-w-5xl"
    >
        <!-- Search & Filters -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div class="relative w-full sm:max-w-md group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <LuSearch class="w-4 h-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors" />
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search icons by name..."
                            class="w-full rounded-xl pl-10 pr-4 py-2.5 text-sm font-medium transition-all duration-200 ease-out border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 shadow-sm"
                        />
                    </div>
                    <div class="px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 shrink-0 w-fit">
                        <p class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                            Showing <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ filteredIcons.length }}</span> of {{ icons.length }}
                        </p>
                    </div>
                </div>

                <!-- Icon Grid -->
                <div v-if="filteredIcons.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                    <div
                        v-for="icon in filteredIcons"
                        :key="icon.name"
                        class="bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl p-4 shadow-sm flex flex-col items-center justify-between gap-3 hover:border-indigo-300 dark:hover:border-indigo-600 transition-colors group"
                    >
                        <div class="p-3 bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 group-hover:scale-110 transition-transform duration-300">
                            <component 
                                :is="icon.component" 
                                :size="24" 
                                class="text-slate-700 dark:text-slate-300 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors" 
                            />
                        </div>
                        
                        <p class="text-[0.65rem] font-mono font-semibold text-slate-600 dark:text-slate-400 text-center break-all line-clamp-2 w-full">
                            {{ icon.name }}
                        </p>

                        <button
                            type="button"
                            @click="copyIconTag(icon.name)"
                            class="w-full flex items-center justify-center gap-1.5 text-[0.6rem] font-bold uppercase tracking-widest px-2 py-1.5 rounded-lg border transition-all active:scale-95"
                            :class="copiedName === icon.name 
                                ? 'bg-emerald-50 text-emerald-600 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/30' 
                                : 'bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 hover:text-indigo-600 dark:hover:text-indigo-400'"
                        >
                            <template v-if="copiedName === icon.name">
                                <LuCheckCircle2 class="w-3 h-3" />
                                <span>Copied</span>
                            </template>
                            <template v-else>
                                <LuCopy class="w-3 h-3" />
                                <span>Copy Tag</span>
                            </template>
                        </button>
                    </div>
                </div>
                
                <!-- Empty State -->
                <div v-else class="flex flex-col items-center justify-center py-16 px-4 text-center bg-slate-50/50 dark:bg-slate-800/30 border border-slate-200/60 dark:border-slate-700/60 rounded-xl border-dashed">
                    <LuShapes class="w-10 h-10 text-slate-300 dark:text-slate-600 mb-3" />
                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">No icons found</p>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Try adjusting your search query.</p>
                </div>

    </TopicLayout>
</template>