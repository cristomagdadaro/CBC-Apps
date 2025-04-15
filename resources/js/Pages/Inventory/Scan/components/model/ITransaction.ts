interface ITransaction extends IBaseClass {
    barcode: string;
    item_id:string;
    transac_type: string;
    quantity: number;
    unit: string;
    unit_price: number;
    total_cost: number;
    personnel_id: string;
    project_code: string;
    user_id: string;
    expiration: string;
    remarks: string;

    item: IItem;
    user: IUser;
    personnel: IPersonnel;
}
