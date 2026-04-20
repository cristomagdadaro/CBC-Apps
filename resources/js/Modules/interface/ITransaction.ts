interface ITransaction extends IBaseClass {
    components?: Array<ITransaction>;
    barcode: string;
    barcode_prri: string;
    parent_barcode?: string;
    item_id:string;
    transac_type: string;
    quantity: number;
    unit: string;
    unit_price: number;
    total_cost: number;
    personnel_id: string;
    employee_id: string;
    user_id: string;
    expiration: string;
    remarks: string;
    project_code: string;
    equipment_logger_mode?: string;
    par_no: string;
    condition: string;
    actor_display_name?: string;

    item: IItem;
    user: IUser;
    personnel: IPersonnel;
}
