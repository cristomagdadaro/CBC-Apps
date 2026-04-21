interface IEquipmentLoggerAsset extends IBaseClass {
    name: string;
    brand: string;
    description: string | null;
    category_id: number;
    category_name: string;
    equipment_logger_mode: string;
    equipment_type: string;
    total_logs: number;
    active_logs: number | string;
    overdue_logs: number | string;
    completed_logs: number | string;
    last_logged_at: Date | string | null;
    barcode: string;
}
