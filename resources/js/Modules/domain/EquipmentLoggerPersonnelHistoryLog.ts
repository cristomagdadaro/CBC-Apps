import DtoLaboratoryEquipmentLog from "@/Modules/dto/DtoLaboratoryEquipmentLog";

export default class EquipmentLoggerPersonnelHistoryLog extends DtoLaboratoryEquipmentLog {
    static endpoints = {
        index: 'api.equipment-logger.personnels.logs.index',
        show: 'transactions.show',
    };

    latest_incoming_transaction_id: string | null;

    constructor(response: any = {}) {
        super(response);

        this.latest_incoming_transaction_id = response?.latest_incoming_transaction_id ?? null;
        this.api._apiIndex = EquipmentLoggerPersonnelHistoryLog.endpoints.index;
        this.showPage = EquipmentLoggerPersonnelHistoryLog.endpoints.show;
        this.showPageParams = this.latest_incoming_transaction_id
            ? { id: this.latest_incoming_transaction_id }
            : null;
        this.showPageTarget = '_blank';
    }

    static getColumns(): any {
        return [
            {
                title: 'Equipment',
                key: 'equipmentName',
                db_key: 'equipment_id',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Brand',
                key: 'equipment.brand',
                db_key: 'equipment_id',
                align: 'text-left',
                sortable: false,
                visible: true,
            },
            {
                title: 'Description',
                key: 'equipment.description',
                db_key: 'equipment_id',
                align: 'text-left',
                sortable: false,
                visible: true,
            },
            {
                title: 'Status',
                key: 'status',
                db_key: 'status',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Location',
                key: 'location_label',
                db_key: 'equipment_id',
                align: 'text-left',
                sortable: false,
                visible: true,
            },
            {
                title: 'Barcode',
                key: 'equipment_barcode',
                db_key: 'equipment_id',
                align: 'text-left',
                sortable: false,
                visible: true,
            },
            {
                title: 'Started At',
                key: 'started_at',
                db_key: 'started_at',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Expected End',
                key: 'end_use_at',
                db_key: 'end_use_at',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Actual End',
                key: 'actual_end_at',
                db_key: 'actual_end_at',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Purpose',
                key: 'purpose',
                db_key: 'purpose',
                align: 'text-left',
                sortable: false,
                visible: true,
            },
        ];
    }
}
