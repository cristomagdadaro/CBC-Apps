import DtoBaseClass from "./DtoBaseClass";
import IEquipmentLoggerAsset from "../interface/IEquipmentLoggerAsset";

export default class DtoEquipmentLoggerAsset extends DtoBaseClass implements IEquipmentLoggerAsset {
    name: string;
    brand: string;
    description: string | null;
    category_id: number;
    category_name: string;
    equipment_logger_mode: string;
    latest_incoming_transaction_id?: string | null;
    equipment_type: string;
    total_logs: number;
    active_logs: number | string;
    overdue_logs: number | string;
    completed_logs: number | string;
    last_logged_at: Date | string | null;
    barcode: string;

    constructor(data: any = {}) {
        super(data);

        this.name = data?.name;
        this.brand = data?.brand;
        this.description = data?.description;
        this.category_id = data?.category_id;
        this.category_name = data?.category_name;
        this.equipment_logger_mode = data?.equipment_logger_mode;
        this.latest_incoming_transaction_id = data?.latest_incoming_transaction_id ?? null;
        this.equipment_type = data?.equipment_type;
        this.total_logs = data?.total_logs;
        this.active_logs = data?.active_logs;
        this.overdue_logs = data?.overdue_logs;
        this.completed_logs = data?.completed_logs;
        this.last_logged_at = data?.last_logged_at;
        this.barcode = data?.barcode;
    }


}
