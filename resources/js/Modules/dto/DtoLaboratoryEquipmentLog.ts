import DtoBaseClass from '@/Modules/dto/DtoBaseClass';
import DtoItem from '@/Modules/dto/DtoItem';
import DtoPersonnel from '@/Modules/dto/DtoPersonnel';

export default class DtoLaboratoryEquipmentLog extends DtoBaseClass {
    equipment_id: string;
    personnel_id: string;
    status: string;
    started_at: string;
    end_use_at: string;
    actual_end_at: string | null;
    purpose: string | null;
    equipment_barcode: string | null;
    location_label: string | null;

    equipment: any;
    personnel: any;

    constructor(data: any = {}) {
        super(data);

        this.equipment_id = data?.equipment_id;
        this.personnel_id = data?.personnel_id;
        this.status = data?.status;
        this.started_at = data?.started_at;
        this.end_use_at = data?.end_use_at;
        this.actual_end_at = data?.actual_end_at ?? null;
        this.purpose = data?.purpose ?? null;
        this.equipment_barcode = data?.equipment_barcode ?? null;
        this.location_label = data?.location_label ?? null;

        this.equipment = data?.equipment ? new DtoItem(data.equipment) : null;
        this.personnel = data?.personnel ? new DtoPersonnel(data.personnel) : null;

        this.api.setSearchFields({
            search: null,
            filter: null,
            filter_by: null,
            is_exact: false,
            page: 1,
            per_page: 25,
            sort: 'started_at',
            order: 'desc',
        });
    }

    get equipmentName(): string {
        return this.equipment?.name || this.equipment_id || '-';
    }

    get personnelName(): string {
        return this.personnel?.fullName || '-';
    }

    get expectedEndAt(): string {
        return this.end_use_at || '-';
    }

    get dataColor(): string {
        return this.status === 'overdue' ? 'text-red-600 text-center uppercase' : 'text-green-600 text-center uppercase';
    }

    identifier(model?: DtoBaseClass): object {
        const source: any = model ?? this;
        return {
            id: source?.equipment_id ?? source?.id,
        };
    }
}
