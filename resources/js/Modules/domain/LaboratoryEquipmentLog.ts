import DtoLaboratoryEquipmentLog from '@/Modules/dto/DtoLaboratoryEquipmentLog';

export default class LaboratoryEquipmentLog extends DtoLaboratoryEquipmentLog {
    static endpoints = {
        index: 'api.laboratory.logs.index',
        show: 'laboratory.equipments.show',
    };
    static showPageTarget = '_blank';

    constructor(response: any = {}) {
        super(response);

        this.api._apiIndex = LaboratoryEquipmentLog.endpoints.index;
        this.api.appendWith = ['equipment', 'personnel'];

        this.showPage = LaboratoryEquipmentLog.endpoints.show;
        this.showPageTarget = LaboratoryEquipmentLog.showPageTarget;
    }

    createFields(): object {
        return {
            equipment_id: null,
            personnel_id: null,
            status: null,
            started_at: null,
            end_use_at: null,
            actual_end_at: null,
            purpose: null,
        };
    }

    updateFields(model: any): object {
        return {
            id: model?.id ?? null,
            equipment_id: model?.equipment_id ?? null,
            personnel_id: model?.personnel_id ?? null,
            status: model?.status ?? null,
            started_at: model?.started_at ?? null,
            end_use_at: model?.end_use_at ?? null,
            actual_end_at: model?.actual_end_at ?? null,
            purpose: model?.purpose ?? null,
        };
    }

    static getColumns(): any {
        return [
            {
                title: 'Equipment',
                key: 'equipmentName',
                db_key: 'equipment_id',
                align: 'dataColor',
                sortable: true,
                visible: true,
            },
            {
                title: 'Status',
                key: 'status',
                db_key: 'status',
                align: 'dataColor',
                sortable: true,
                visible: true,
            },
            {
                title: 'Location',
                key: 'location_label',
                db_key: 'equipment_id',
                align: 'dataColor',
                sortable: false,
                visible: true,
            },
            {
                title: 'Barcode',
                key: 'equipment_barcode',
                db_key: 'equipment_id',
                align: 'dataColor',
                sortable: false,
                visible: true,
            },
            {
                title: 'Personnel',
                key: 'personnelName',
                db_key: 'personnel_id',
                align: 'dataColor',
                sortable: false,
                visible: true,
            },
            {
                title: 'Started At',
                key: 'started_at',
                db_key: 'started_at',
                align: 'dataColor',
                sortable: true,
                visible: true,
            },
            {
                title: 'Expected End',
                key: 'expectedEndAt',
                db_key: 'end_use_at',
                align: 'dataColor',
                sortable: true,
                visible: true,
            },
        ];
    }
}
