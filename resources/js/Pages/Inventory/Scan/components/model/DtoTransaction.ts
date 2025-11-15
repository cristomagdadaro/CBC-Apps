import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import DtoItem from "@/Pages/Inventory/Items/components/model/DtoItem";
import DtoPersonnel from "@/Pages/Inventory/Personnel/components/model/DtoPersonnel";
import DtoUser from "@/Modules/dto/DtoUser";

export default class DtoTransaction extends DtoBaseClass implements ITransaction{
    barcode: string;
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

    item: IItem;
    user: IUser;
    personnel: IPersonnel;

    constructor(data: ITransaction) {
        super(data);

        this.barcode = data?.barcode;
        this.item_id = data?.item_id;
        this.transac_type = data?.transac_type;
        this.quantity = data?.quantity;
        this.unit = data?.unit;
        this.unit_price = data?.unit_price;
        this.total_cost = data?.total_cost;
        this.personnel_id = data?.personnel_id;
        this.employee_id = data?.employee_id;
        this.user_id = data?.user_id;
        this.expiration = data?.expiration;
        this.remarks = data?.remarks;

        if (data?.item)
            this.item = new DtoItem(data?.item);

        if (data?.user)
            this.user = new DtoUser(data?.user);

        if (data?.personnel)
            this.personnel = new DtoPersonnel(data.personnel)
    }
}
