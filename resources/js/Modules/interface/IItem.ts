interface IItem extends IBaseClass {
    id: string;
    name: string;
    brand: string;
    description: string;
    specifications: string;
    category_id: string;
    supplier_id: string;
    image: string;
    simultaneous_users: number;

    supplier: ISupplier;
    category: ICategory;
}
