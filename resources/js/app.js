import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { Head, Link } from '@inertiajs/vue3';

// Layout
import AppLayout from './Layouts/AppLayout.vue';

// Common Form Components
import TextInput from "@/Components/TextInput.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Checkbox from "@/Components/Checkbox.vue";
import TextArea from "@/Components/TextArea.vue";
import DateInput from "@/Components/DateInput.vue";
import TimeInput from "@/Components/TimeInput.vue";
import FileInput from "@/Components/FileInput.vue";

// Button Components
import SubmitBtn from "@/Components/Buttons/SubmitBtn.vue";
import ResetBtn from "@/Components/Buttons/ResetBtn.vue";
import AddButton from "@/Components/Buttons/AddButton.vue";
import DeleteBtn from "@/Components/Buttons/DeleteBtn.vue";
import CancelBtn from "@/Components/Buttons/CancelBtn.vue";
import SearchBtn from "@/Components/Buttons/SearchBtn.vue";
import ScanBtn from "@/Components/Buttons/ScanBtn.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";

// Dropdown Components
import DropdownLink from "@/Components/DropdownLink.vue";
import Dropdown from "@/Components/Dropdown.vue";
import CustomDropdown from "@/Components/CustomDropdown/CustomDropdown.vue";

// Modal Components
import Modal from "@/Components/Modal.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import DeleteConfirmationModal from "@/Components/DeleteConfirmationModal.vue";
import DialogModal from "@/Components/DialogModal.vue";
import SuccessModal from "@/Components/SuccessModal.vue";
import ConfirmsPassword from "@/Components/ConfirmsPassword.vue";

// Layout Components
import SectionBorder from "@/Components/SectionBorder.vue";
import SectionTitle from "@/Components/SectionTitle.vue";
import FormSection from "@/Components/FormSection.vue";
import ActionSection from "@/Components/ActionSection.vue";
import ActionMessage from "@/Components/ActionMessage.vue";

// Icon & UI Components
import PaginateBtn from "@/Components/PaginateBtn.vue";
import TabNavigation from "@/Components/TabNavigation.vue";
import ProgressTabs from "@/Components/ProgressTabs.vue";
import SearchBy from "@/Components/SearchBy.vue";
import LikertScale from "@/Components/LikertScale.vue";

// Transitions & Animation
import TransitionContainer from "@/Components/Transitions/TransitionContrainer.vue";
import DataTable from '@/Modules/DataTable/presentation/DataTable.vue';
import SearchComp from '@/Components/Search/SearchComp.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) });

        // Register global components
        vueApp.component('Head', Head);
        vueApp.component('Link', Link);
        vueApp.component('AppLayout', AppLayout);

        // Global Form Components
        vueApp.component('TextInput', TextInput);
        vueApp.component('InputError', InputError);
        vueApp.component('InputLabel', InputLabel);
        vueApp.component('Checkbox', Checkbox);
        vueApp.component('TextArea', TextArea);
        vueApp.component('DateInput', DateInput);
        vueApp.component('TimeInput', TimeInput);
        vueApp.component('FileInput', FileInput);

        // Global Button Components
        vueApp.component('SubmitBtn', SubmitBtn);
        vueApp.component('ResetBtn', ResetBtn);
        vueApp.component('AddButton', AddButton);
        vueApp.component('DeleteBtn', DeleteBtn);
        vueApp.component('CancelBtn', CancelBtn);
        vueApp.component('SearchBtn', SearchBtn);
        vueApp.component('ScanBtn', ScanBtn);
        vueApp.component('PrimaryButton', PrimaryButton);
        vueApp.component('SecondaryButton', SecondaryButton);
        vueApp.component('DangerButton', DangerButton);

        // Global Dropdown Components
        vueApp.component('DropdownLink', DropdownLink);
        vueApp.component('Dropdown', Dropdown);
        vueApp.component('CustomDropdown', CustomDropdown);

        // Global Modal Components
        vueApp.component('Modal', Modal);
        vueApp.component('ConfirmationModal', ConfirmationModal);
        vueApp.component('DeleteConfirmationModal', DeleteConfirmationModal);
        vueApp.component('DialogModal', DialogModal);
        vueApp.component('SuccessModal', SuccessModal);
        vueApp.component('ConfirmsPassword', ConfirmsPassword);

        // Global Layout Components
        vueApp.component('SectionBorder', SectionBorder);
        vueApp.component('SectionTitle', SectionTitle);
        vueApp.component('FormSection', FormSection);
        vueApp.component('ActionSection', ActionSection);
        vueApp.component('ActionMessage', ActionMessage);

        // Global Icon & UI Components
        vueApp.component('PaginateBtn', PaginateBtn);
        vueApp.component('TabNavigation', TabNavigation);
        vueApp.component('ProgressTabs', ProgressTabs);
        vueApp.component('SearchBy', SearchBy);
        vueApp.component('LikertScale', LikertScale);

        // Global Transitions & Animation
        vueApp.component('TransitionContainer', TransitionContainer);
        vueApp.component('DataTable', DataTable);
        vueApp.component('SearchComp', SearchComp);
        

        // Set global properties
        vueApp.config.globalProperties.$appVersion = import.meta.env.VITE_APP_VERSION;
        vueApp.config.globalProperties.$appName = import.meta.env.VITE_APP_NAME;
        vueApp.config.globalProperties.$companyName = import.meta.env.VITE_COMPANY_NAME;
        vueApp.config.globalProperties.$companyNameShort = import.meta.env.VITE_COMPANY_NAME_SHORT;
        vueApp.config.globalProperties.$appEnv = import.meta.env.VITE_APP_ENV;

        if (typeof window !== 'undefined') {
            window.__APP_ENV__ = import.meta.env.VITE_APP_ENV || import.meta.env.MODE || 'production';
        }

        return vueApp
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
