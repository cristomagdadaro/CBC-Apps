interface IItem extends IBaseClass {
    id: string;
    name: string;
    brand: string;
    description: string;
    category_id: string;
    supplier_id: string;
    image: string;

    supplier: ISupplier;
    category: ICategory;
}
