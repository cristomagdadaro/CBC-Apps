import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import DtoTransaction from "@/Modules/dto/DtoTransaction";
import DtoItem from "@/Modules/dto/DtoItem";
import DtoUser from "@/Modules/dto/DtoUser";

export default class DtoSuppEquipReport extends DtoBaseClass implements ISuppEquipReport {
    transaction_id: string;
    item_id: string | null;
    user_id: number | null;
    report_type: string;
    report_data: Record<string, any>;
    notes: string | null;
    reported_at: string | null;

    transaction?: ITransaction;
    item?: IItem;
    user?: IUser;

    constructor(data: ISuppEquipReport) {
        super(data);

        this.transaction_id = data?.transaction_id;
        this.item_id = data?.item_id ?? null;
        this.user_id = data?.user_id ?? null;
        this.report_type = data?.report_type;
        this.report_data = data?.report_data ?? {};
        this.notes = data?.notes ?? null;
        this.reported_at = data?.reported_at ?? null;

        if (data?.transaction) {
            this.transaction = new DtoTransaction(data.transaction);
        }

        if (data?.item) {
            this.item = new DtoItem(data.item);
        }

        if (data?.user) {
            this.user = new DtoUser(data.user);
        }
    }

    get reportSummary(): string {
        if (!this.report_data) {
            return '';
        }

        const firstKey = Object.keys(this.report_data)[0];
        if (!firstKey) {
            return '';
        }

        return `${firstKey}: ${this.report_data[firstKey]}`;
    }

    get getBarcode(): string {
        return this.transaction?.barcode ?? null;
    }
}
