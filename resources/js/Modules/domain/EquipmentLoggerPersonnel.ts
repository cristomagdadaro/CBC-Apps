import DtoEquipmentLoggerPersonnel from "@/Modules/dto/DtoEquipmentLoggerPersonnel";

export default class EquipmentLoggerPersonnel extends DtoEquipmentLoggerPersonnel {
    static endpoints = {
        index: 'api.equipment-logger.personnels.index',
        show: 'equipment-logger.personnels.show',
    };

    constructor(response: any = {}) {
        super(response);

        this.api._apiIndex = EquipmentLoggerPersonnel.endpoints.index;
        this.showPage = EquipmentLoggerPersonnel.endpoints.show;
        this.showPageParams = this.id
            ? { personnelId: this.id }
            : null;
    }

    static getColumns(): any {
        return [
            {
                title: 'Employee ID',
                key: 'employee_id',
                db_key: 'employee_id',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Name',
                key: 'fullName',
                db_key: 'fname',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Position',
                key: 'position',
                db_key: 'position',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Email',
                key: 'email',
                db_key: 'email',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Total Logs',
                key: 'total_logs',
                db_key: 'total_logs',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Active',
                key: 'active_logs',
                db_key: 'active_logs',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Overdue',
                key: 'overdue_logs',
                db_key: 'overdue_logs',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Completed',
                key: 'completed_logs',
                db_key: 'completed_logs',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Last Logged',
                key: 'last_logged_at',
                db_key: 'last_logged_at',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
        ];
    }
}
