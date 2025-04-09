import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoTransaction extends DtoBaseClass implements ITransaction{
    id: string;
    brand: string;
    unit: string;
    remaining_quantity: number;
    total_outgoing: number;
    total_cost: number;

    constructor(data: ITransaction) {
        super(data);

        this.id = data.id ?? null;
        this.brand = data.brand ?? null;
        this.unit = data.unit ?? null;
        this.remaining_quantity = data.remaining_quantity ?? null;
        this.total_outgoing = data.total_outgoing ?? null;
        this.total_cost = data.total_cost ?? null;
    }
}
