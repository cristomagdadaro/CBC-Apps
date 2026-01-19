interface ISuppEquipReport extends IBaseClass {
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
}
