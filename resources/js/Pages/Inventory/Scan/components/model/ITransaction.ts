interface ITransaction extends IBaseClass {
    id: string;
    brand: string;
    unit: string;
    remaining_quantity: number;
    total_outgoing: number;
    total_cost: number;
}
