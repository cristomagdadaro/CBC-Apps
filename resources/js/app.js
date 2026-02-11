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
import SelectSearchField from "@/Components/SelectSearchField.vue";
import SelectSex from '@/Components/SelectSex.vue';
import SelectRegion from "@/Components/SelectRegion.vue";
import SelectProvince from "@/Components/SelectProvince.vue";
import SelectCity from "@/Components/SelectCity.vue";
import PersonnelLookup from "@/Components/PersonnelLookup.vue";

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
import NavLink from "@/Components/NavLink.vue";

// Icon Components
import AddIcon from "@/Components/Icons/AddIcon.vue";
import ArrowLeft from "@/Components/Icons/ArrowLeft.vue";
import ArrowRight from "@/Components/Icons/ArrowRight.vue";
import BellIcon from './Components/Icons/BellIcon.vue';
import CaretDown from './Components/Icons/CaretDown.vue';
import CaretUp from './Components/Icons/CaretUp.vue';
import CheckallIcon from './Components/Icons/CheckallIcon.vue';
import CircleOneIcon from './Components/Icons/CircleOneIcon.vue';
import Close from './Components/Icons/Close.vue';
import CloseIcon from './Components/Icons/CloseIcon.vue';
import CollapseIcon from './Components/Icons/CollapseIcon.vue';
import ConeIcon from './Components/Icons/ConeIcon.vue';
import DeleteIcon from './Components/Icons/DeleteIcon.vue';
import DeselectIcon from './Components/Icons/DeselectIcon.vue';
import DoubleArrowLoaderIcon from './Components/Icons/DoubleArrowLoaderIcon.vue';
import DownloadIcon from './Components/Icons/DownloadIcon.vue';
import EditIcon from './Components/Icons/EditIcon.vue';
import ErrorIcon from './Components/Icons/ErrorIcon.vue';
import ExclamationCircleIcon from './Components/Icons/ExclamationCircleIcon.vue';
import ExpandIcon from './Components/Icons/ExpandIcon.vue';
import ExportIcon from './Components/Icons/ExportIcon.vue';
import FailedIcon from './Components/Icons/FailedIcon.vue';
import FilterIcon from './Components/Icons/FilterIcon.vue';
import FullScreenIcon from './Components/Icons/FullScreenIcon.vue';
import Hamburger from './Components/Icons/Hamburger.vue';
import ImportIcon from './Components/Icons/ImportIcon.vue';
import InfoIcon from './Components/Icons/InfoIcon.vue';
import LoaderIcon from './Components/Icons/LoaderIcon.vue';
import Logo from './Components/Icons/Logo.vue';
import PhilippineMapOutline from './Components/Icons/PhilippineMapOutline.vue';
import PrinterIcon from './Components/Icons/PrinterIcon.vue';
import ProfileEmptyIcon from './Components/Icons/ProfileEmptyIcon.vue';
import RefreshIcon from './Components/Icons/RefreshIcon.vue';
import SearchIcon from './Components/Icons/SearchIcon.vue';
import ShareIcon from './Components/Icons/ShareIcon.vue';
import SpinnerIcon from './Components/Icons/SpinnerIcon.vue';
import SuccessIcon from './Components/Icons/SuccessIcon.vue';
import ThreeDotIcon from './Components/Icons/ThreeDotIcon.vue';
import ToggleOffIcon from './Components/Icons/ToggleOffIcon.vue';
import ToggleOnIcon from './Components/Icons/ToggleOnIcon.vue';
import UnviewIcon from './Components/Icons/UnviewIcon.vue';
import UploadIcon from './Components/Icons/UploadIcon.vue';
import ViewIcon from './Components/Icons/ViewIcon.vue';
import WarningIcon from './Components/Icons/WarningIcon.vue';
import ScanIcon from './Components/Icons/ScanIcon.vue';
import SettingIcon from './Components/Icons/SettingIcon.vue';
import BookmarkIcon from './Components/Icons/BookmarkIcon.vue';
import CalendarIcon from './Components/Icons/CalendarIcon.vue';
import FesIcon from './Components/Icons/FesIcon.vue';
import FlagIcon from './Components/Icons/FlagIcon.vue';
import BoxesIcon from './Components/Icons/BoxesIcon.vue';
import TruckIcon from './Components/Icons/TruckIcon.vue';
import BuildingIcon from './Components/Icons/BuildingIcon.vue';

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
import GuestFormPage from "@/Pages/Shared/GuestFormPage.vue";

// Icon & UI Components
import PaginateBtn from "@/Components/PaginateBtn.vue";
import TabNavigation from "@/Components/TabNavigation.vue";
import ProgressTabs from "@/Components/ProgressTabs.vue";
import SearchBy from "@/Components/SearchBy.vue";
import SearchBox from "@/Components/Search/searchBox.vue";
import LikertScale from "@/Components/LikertScale.vue";
import GuestCard from "@/Pages/Forms/components/GuestCard.vue";

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
        vueApp.component('SelectSearchField', SelectSearchField);
        vueApp.component('SelectSex', SelectSex);
        vueApp.component('SelectRegion', SelectRegion);
        vueApp.component('SelectProvince', SelectProvince);
        vueApp.component('SelectCity', SelectCity);
        vueApp.component('PersonnelLookup', PersonnelLookup);

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
        vueApp.component('NavLink', NavLink);

        // Global Icon Components
        vueApp.component('AddIcon', AddIcon);
        vueApp.component('ArrowLeft', ArrowLeft);
        vueApp.component('ArrowRight', ArrowRight);
        vueApp.component('BellIcon', BellIcon);
        vueApp.component('CaretDown', CaretDown);
        vueApp.component('CaretUp', CaretUp);
        vueApp.component('CheckallIcon', CheckallIcon);
        vueApp.component('CircleOneIcon', CircleOneIcon);
        vueApp.component('Close', Close);
        vueApp.component('CloseIcon', CloseIcon);
        vueApp.component('CollapseIcon', CollapseIcon);
        vueApp.component('ConeIcon', ConeIcon);
        vueApp.component('DeleteIcon', DeleteIcon);
        vueApp.component('DeselectIcon', DeselectIcon);
        vueApp.component('DoubleArrowLoaderIcon', DoubleArrowLoaderIcon);
        vueApp.component('DownloadIcon', DownloadIcon);
        vueApp.component('EditIcon', EditIcon);
        vueApp.component('ErrorIcon', ErrorIcon);
        vueApp.component('ExclamationCircleIcon', ExclamationCircleIcon);
        vueApp.component('ExpandIcon', ExpandIcon);
        vueApp.component('ExportIcon', ExportIcon);
        vueApp.component('FailedIcon', FailedIcon);
        vueApp.component('FilterIcon', FilterIcon);
        vueApp.component('FullScreenIcon', FullScreenIcon);
        vueApp.component('Hamburger', Hamburger);
        vueApp.component('ImportIcon', ImportIcon);
        vueApp.component('InfoIcon', InfoIcon);
        vueApp.component('LoaderIcon', LoaderIcon);
        vueApp.component('Logo', Logo);
        vueApp.component('PhilippineMapOutline', PhilippineMapOutline);
        vueApp.component('PrinterIcon', PrinterIcon);
        vueApp.component('ProfileEmptyIcon', ProfileEmptyIcon);
        vueApp.component('RefreshIcon', RefreshIcon);
        vueApp.component('SearchIcon', SearchIcon);
        vueApp.component('ShareIcon', ShareIcon);
        vueApp.component('SpinnerIcon', SpinnerIcon);
        vueApp.component('SuccessIcon', SuccessIcon);
        vueApp.component('ThreeDotIcon', ThreeDotIcon);
        vueApp.component('ToggleOffIcon', ToggleOffIcon);
        vueApp.component('ToggleOnIcon', ToggleOnIcon);
        vueApp.component('UnviewIcon', UnviewIcon);
        vueApp.component('UploadIcon', UploadIcon);
        vueApp.component('ViewIcon', ViewIcon);
        vueApp.component('WarningIcon', WarningIcon);
        vueApp.component('ScanIcon', ScanIcon);
        vueApp.component('SettingIcon', SettingIcon);
        vueApp.component('BookmarkIcon', BookmarkIcon);
        vueApp.component('CalendarIcon', CalendarIcon);
        vueApp.component('FesIcon', FesIcon);
        vueApp.component('FlagIcon', FlagIcon);
        vueApp.component('BoxesIcon', BoxesIcon);
        vueApp.component('TruckIcon', TruckIcon);
        vueApp.component('BuildingIcon', BuildingIcon);

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
        vueApp.component('GuestFormPage', GuestFormPage);

        // Global Icon & UI Components
        vueApp.component('PaginateBtn', PaginateBtn);
        vueApp.component('TabNavigation', TabNavigation);
        vueApp.component('ProgressTabs', ProgressTabs);
        vueApp.component('SearchBy', SearchBy);
        vueApp.component('SearchBox', SearchBox);
        vueApp.component('LikertScale', LikertScale);
        vueApp.component('GuestCard', GuestCard);

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
