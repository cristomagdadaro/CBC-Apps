import DtoBaseClass from "./DtoBaseClass";

export default class DtoEquipmentLoggerPersonnel extends DtoBaseClass {
    employee_id: string | null;
    fname: string | null;
    mname: string | null;
    lname: string | null;
    suffix: string | null;
    position: string | null;
    phone: string | null;
    email: string | null;
    total_logs: number;
    active_logs: number;
    overdue_logs: number;
    completed_logs: number;
    last_logged_at: string | null;

    constructor(data: any = {}) {
        super(data);

        this.employee_id = data?.employee_id ?? null;
        this.fname = data?.fname ?? null;
        this.mname = data?.mname ?? null;
        this.lname = data?.lname ?? null;
        this.suffix = data?.suffix ?? null;
        this.position = data?.position ?? null;
        this.phone = data?.phone ?? null;
        this.email = data?.email ?? null;
        this.total_logs = Number(data?.total_logs ?? 0);
        this.active_logs = Number(data?.active_logs ?? 0);
        this.overdue_logs = Number(data?.overdue_logs ?? 0);
        this.completed_logs = Number(data?.completed_logs ?? 0);
        this.last_logged_at = data?.last_logged_at ?? null;

        this.api.setSearchFields({
            search: null,
            filter: null,
            filter_by: null,
            is_exact: false,
            page: 1,
            per_page: 25,
            sort: 'total_logs',
            order: 'desc',
        });
    }

    get fullName(): string {
        return [this.fname, this.mname, this.lname, this.suffix]
            .filter(Boolean)
            .join(' ');
    }
}
