interface ITransaction extends IBaseClass {
    barcode: string;
    barcode_prri: string;
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
    par_no: string;
    condition: string;

    item: IItem;
    user: IUser;
    personnel: IPersonnel;
}
