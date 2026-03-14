<template>
    <div class="space-y-6">
        <section>
            <h3 class="text-lg font-bold mb-3">Available Icons</h3>
            <p class="mb-3">
                This list is generated from <code>resources/js/Components/Icons/index.ts</code>.
                Any new icon exported there appears here automatically.
            </p>
            <div class="mb-3">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search icons..."
                    class="w-full sm:w-96 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 focus:border-blue-500 focus:ring-blue-500"
                />
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                Showing <strong>{{ filteredIcons.length }}</strong> of <strong>{{ icons.length }}</strong> icons
            </p>
        </section>

        <section class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            <div
                v-for="icon in filteredIcons"
                :key="icon.name"
                class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 flex flex-col items-center gap-2"
            >
                <component :is="icon.component" :size="22" />
                <p class="text-xs text-center break-all">{{ icon.name }}</p>
                <button
                    type="button"
                    class="text-xs px-2 py-1 rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                    @click="copyIconTag(icon.name)"
                >
                    {{ copiedName === icon.name ? 'Copied!' : 'Copy tag' }}
                </button>
            </div>
        </section>
    </div>
</template>

<script>
import * as Icons from '@/Components/Icons'

export default {
    name: 'IconsLibraryTopic',
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
