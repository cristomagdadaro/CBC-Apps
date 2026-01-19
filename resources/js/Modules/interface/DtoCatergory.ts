import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoCategory extends DtoBaseClass implements ICategory {
    name: string;
    description: string;

    constructor(data: ICategory) {
        super(data);

        this.name = data?.name;
        this.description = data?.description;
    }
}
