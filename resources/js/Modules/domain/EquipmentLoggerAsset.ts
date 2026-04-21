import DtoEquipmentLoggerAsset from "../dto/DtoEquipmentLoggerAsset";

export default class EquipmentLoggerAsset extends DtoEquipmentLoggerAsset {
    static endpoints = {
        index: "api.equipment-logger.equipments.index",
        put: 'api.inventory.transactions.update',
        showPageIct: 'ict.equipments.show',
        showPageLab: 'laboratory.equipments.show'
    };

    static showPageTarget = '_blank';

    constructor(response: any = {}) {
        super(response);

        this.api._apiIndex = EquipmentLoggerAsset.endpoints.index;
        this.api._apiPut = EquipmentLoggerAsset.endpoints.put;
        this.showPage = this.equipment_type === 'ict' ? EquipmentLoggerAsset.endpoints.showPageIct : EquipmentLoggerAsset.endpoints.showPageLab;
        this.showPageTarget = EquipmentLoggerAsset.showPageTarget;
    }

    static getColumns(): any {
        return [
            {
                title: "Equipment",
                key: "name",
                db_key: "name",
                align: "dataColor",
                sortable: true,
                visible: true,
            },
            {
                title: "Group",
                key: "equipment_type",
                db_key: "equipment_type",
                align: "dataColor",
                sortable: false,
                visible: true,
            },
            {
                title: "Category",
                key: "category_name",
                db_key: "category_name",
                align: "dataColor",
                sortable: true,
                visible: false,
            },
            {
                title: "Logger Mode",
                key: "equipment_logger_mode",
                db_key: "equipment_logger_mode",
                align: "dataColor",
                sortable: true,
                visible: true,
            },
            {
                title: "Barcode",
                key: "barcode",
                db_key: "barcode",
                align: "dataColor",
                sortable: false,
                visible: false,
            },
            {
                title: "Total Logs",
                key: "total_logs",
                db_key: "total_logs",
                align: "dataColor",
                sortable: true,
                visible: true,
            },
            {
                title: "Active",
                key: "active_logs",
                db_key: "active_logs",
                align: "dataColor",
                sortable: true,
                visible: true,
            },
            {
                title: "Overdue",
                key: "overdue_logs",
                db_key: "overdue_logs",
                align: "dataColor",
                sortable: true,
                visible: true,
            },
            {
                title: "Completed",
                key: "completed_logs",
                db_key: "completed_logs",
                align: "dataColor",
                sortable: true,
                visible: true,
            },
            {
                title: "Last Logged",
                key: "last_logged_at",
                db_key: "last_logged_at",
                align: "dataColor",
                sortable: true,
                visible: true,
            },
        ];
    }
}
