import DtoSuppEquipReport from "@/Modules/dto/DtoSuppEquipReport";
import {usePage} from "@inertiajs/vue3";

export default class SuppEquipReport extends DtoSuppEquipReport {
    constructor(response: DtoSuppEquipReport = {} as DtoSuppEquipReport) {
        super(response);

        const page = usePage();
        this.api._apiIndex = (!!page.props.auth?.user) ? 'api.inventory.supp_equip_reports.index':'api.inventory.transactions.index.public';
        console.log(this.api._apiIndex);
        this.api._apiPost = 'api.inventory.supp_equip_reports.store' ;
        this.api._apiPut = 'api.inventory.supp_equip_reports.update';
        this.api._apiDelete = 'api.inventory.supp_equip_reports.destroy';

        this.api.appendWith = ['transaction.item', 'transaction.user', 'item', 'user'];
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

    deleteField(model: ISuppEquipReport): object {
        return {
            id: model?.id,
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
                visible: true,
            },
            {
                title: 'Barcode',
                key: 'transaction.barcode',
                db_key: 'barcode',
                align: 'text-left',
                sortable: true,
                visible: true,
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
                visible: true,
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
                visible: true,
            },
        ];
    }
}
