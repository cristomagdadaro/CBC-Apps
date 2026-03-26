import type { App, Component } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

import * as Components from '@/Components';
import * as Icons from '@/Components/Icons';
import * as Layouts from '@/Layouts';
import DataTable from '@/Modules/DataTable/presentation/DataTable.vue';
import * as SharedPages from '@/Pages/Shared';

const registerComponentGroup = (app: App, components: Record<string, unknown>): void => {
    Object.entries(components).forEach(([name, component]) => {
        app.component(name, component as Component);
    });
};

export const registerGlobalComponents = (app: App): void => {
    registerComponentGroup(app, { Head, Link });
    registerComponentGroup(app, Layouts);
    registerComponentGroup(app, SharedPages);
    registerComponentGroup(app, Components);
    registerComponentGroup(app, Icons);
    app.component('DataTable', DataTable);
};