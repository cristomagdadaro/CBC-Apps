import DtoSuppEquipReport from "@/Modules/dto/DtoSuppEquipReport";
import {usePage} from "@inertiajs/vue3";

export default class SuppEquipReport extends DtoSuppEquipReport {
    static endpoints = {
        index: 'api.inventory.supp_equip_reports.index',
        postAuth: 'api.inventory.supp_equip_reports.store',
        postGuest: 'api.inventory.supp_equip_reports.store.public',
        put: 'api.inventory.supp_equip_reports.update',
        delete: 'api.inventory.supp_equip_reports.destroy',
        create: 'suppEquipReports.create',
        show: 'suppEquipReports.show',
    }
    constructor(response: DtoSuppEquipReport = {} as DtoSuppEquipReport) {
        super(response);

        const page = usePage();
        const isAuthenticated = !!(page.props as any)?.auth?.user;

        this.api._apiIndex = SuppEquipReport.endpoints.index;
        this.api._apiPost = isAuthenticated ? SuppEquipReport.endpoints.postAuth : SuppEquipReport.endpoints.postGuest;
        this.api._apiPut = SuppEquipReport.endpoints.put;
        this.api._apiDelete = SuppEquipReport.endpoints.delete;

        this.api.appendWith = ['transaction.item', 'transaction.user', 'transaction.personnel', 'item', 'user'];
        this.createPage = SuppEquipReport.endpoints.create;
        this.showPage = SuppEquipReport.endpoints.show;
    }

    createFields(): object {
        return {
            transaction_id: null,
            report_type: null,
            report_data: {},
            notes: null,
            reported_at: new Date().toISOString().slice(0, 10),
        };
    }

    updateFields(data: ISuppEquipReport): object {
        return {
            id: data?.id,
            transaction_id: data?.transaction_id,
            report_type: data?.report_type,
            report_data: data?.report_data,
            notes: data?.notes,
            reported_at: data?.reported_at,
        };
    }

    static getColumns() {
        return [
            {
                title: 'Report ID',
                key: 'id',
                db_key: 'id',
                align: 'text-center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Template',
                key: 'report_type',
                db_key: 'report_type',
                align: 'text-left',
                sortable: true,
                visible: false,
            },
            {
                title: 'Barcode',
                key: 'transaction.barcode',
                db_key: 'barcode',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Property No.',
                key: 'transaction.barcode_prri',
                db_key: 'barcode_prri',
                align: 'text-center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Item',
                key: 'transaction.item.fullName',
                db_key: 'item_id',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Transaction Type',
                key: 'transaction.transac_type',
                db_key: 'transac_type',
                align: 'text-center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Reported At',
                key: 'reported_at',
                db_key: 'reported_at',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Filed By',
                key: 'user.name',
                db_key: 'user_id',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Notes',
                key: 'notes',
                db_key: 'notes',
                align: 'text-left',
                sortable: false,
                visible: false,
            },
            {
                title: 'Problem Summary',
                key: 'report_data.impact_summary',
                db_key: 'impact_summary',
                align: 'text-left',
                sortable: false,
                visible: true,
            },
            {
                title: 'Action Taken',
                key: 'report_data.immediate_action',
                db_key: 'immediate_action',
                align: 'text-left',
                sortable: false,
                visible: true,
            },
            {
                title: 'Status',
                key: 'report_data.status',
                db_key: 'status',
                align: 'text-left uppercase',
                sortable: false,
                visible: true,
            },
        ];
    }
}
