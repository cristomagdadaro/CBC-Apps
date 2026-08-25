<script>
import * as Icons from "@/Components/Icons";
import TopicLayout from "../Components/TopicLayout.vue";

export default {
    name: "IconsLibraryTopic",
    components: {
        TopicLayout,
    },
    data() {
        return {
            search: "",
            copiedName: "",
        };
    },
    computed: {
        icons() {
            return Object.entries(Icons)
                .map(([name, component]) => ({ name, component }))
                .sort((a, b) => a.name.localeCompare(b.name));
        },
        filteredIcons() {
            const query = this.search.trim().toLowerCase();

            if (!query) {
                return this.icons;
            }

            return this.icons.filter((icon) => {
                const kebab = this.toKebabCase(icon.name);
                return icon.name.toLowerCase().includes(query) || kebab.includes(query);
            });
        },
    },
    methods: {
        toKebabCase(value) {
            return value
                .replace(/([A-Z])([A-Z][a-z])/g, "$1-$2")
                .replace(/([a-z0-9])([A-Z])/g, "$1-$2")
                .toLowerCase();
        },
        async copyIconTag(iconName) {
            const tag = `<${this.toKebabCase(iconName)} />`;

            if (navigator?.clipboard?.writeText) {
                await navigator.clipboard.writeText(tag);
            } else {
                const textArea = document.createElement("textarea");
                textArea.value = tag;
                textArea.style.position = "fixed";
                textArea.style.opacity = "0";
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand("copy");
                document.body.removeChild(textArea);
            }

            this.copiedName = iconName;
            setTimeout(() => {
                if (this.copiedName === iconName) {
                    this.copiedName = "";
                }
            }, 1200);
        },
    },
};
</script>

<template>
    <TopicLayout
        title="Available Icons"
        description="This list is automatically generated from resources/js/Components/Icons/index.ts. Any new icon exported there appears here automatically."
        icon="LuShapes"
        maxWidth="max-w-5xl">
        <!-- Search & Filters -->
        <div class="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div class="group relative w-full sm:max-w-md">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                    <LuSearch class="h-4 w-4 text-slate-400 transition-colors group-focus-within:text-indigo-500" />
                </div>
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search icons by name..."
                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm font-medium text-slate-900 shadow-sm transition-all duration-200 ease-out placeholder:text-slate-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500" />
            </div>
            <div class="w-fit shrink-0 rounded-lg border border-slate-100 bg-slate-50 px-3 py-1.5 dark:border-slate-700 dark:bg-slate-800/50">
                <p class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-500 dark:text-slate-400">
                    Showing
                    <span class="font-bold text-indigo-600 dark:text-indigo-400">
                        {{ filteredIcons.length }}
                    </span>
                    of {{ icons.length }}
                </p>
            </div>
        </div>

        <!-- Icon Grid -->
        <div
            v-if="filteredIcons.length > 0"
            class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
            <div
                v-for="icon in filteredIcons"
                :key="icon.name"
                class="group flex flex-col items-center justify-between gap-3 rounded-xl border border-slate-200/60 bg-slate-50/50 p-4 shadow-sm transition-colors hover:border-indigo-300 dark:border-slate-700/60 dark:bg-slate-800/30 dark:hover:border-indigo-600">
                <div class="rounded-xl border border-slate-100 bg-white p-3 shadow-sm transition-transform duration-300 group-hover:scale-110 dark:border-slate-700 dark:bg-slate-800">
                    <component
                        :is="icon.component"
                        :size="24"
                        class="text-slate-700 transition-colors group-hover:text-indigo-600 dark:text-slate-300 dark:group-hover:text-indigo-400" />
                </div>

                <p class="line-clamp-2 w-full break-all text-center font-mono text-[0.65rem] font-semibold text-slate-600 dark:text-slate-400">
                    {{ icon.name }}
                </p>

                <button
                    type="button"
                    @click="copyIconTag(icon.name)"
                    class="flex w-full items-center justify-center gap-1.5 rounded-lg border px-2 py-1.5 text-[0.6rem] font-bold uppercase tracking-widest transition-all active:scale-95"
                    :class="copiedName === icon.name ? 'border-emerald-200 bg-emerald-50 text-emerald-600 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400' : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700 dark:hover:text-indigo-400'">
                    <template v-if="copiedName === icon.name">
                        <LuCheckCircle2 class="h-3 w-3" />
                        <span>Copied</span>
                    </template>
                    <template v-else>
                        <LuCopy class="h-3 w-3" />
                        <span>Copy Tag</span>
                    </template>
                </button>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-else
            class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200/60 bg-slate-50/50 px-4 py-16 text-center dark:border-slate-700/60 dark:bg-slate-800/30">
            <LuShapes class="mb-3 h-10 w-10 text-slate-300 dark:text-slate-600" />
            <p class="text-sm font-semibold text-slate-600 dark:text-slate-300">No icons found</p>
            <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-400">Try adjusting your search query.</p>
        </div>
    </TopicLayout>
</template>
