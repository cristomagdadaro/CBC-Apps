import DtoUser from "@/Modules/dto/DtoUser";

export default class User extends DtoUser {
    static endpoints = {
        index: 'api.users.index',
        put: 'api.users.update',
        delete: 'api.users.destroy',
        show: 'system.users.show',
    };

    constructor(response: DtoUser) {
        super(response);

        this.api._apiIndex = User.endpoints.index;
        this.api._apiPut = User.endpoints.put;
        this.api._apiDelete = User.endpoints.delete;
        
        this.showPage = User.endpoints.show;

        this.api.appendWith = ['roles'];
    }

    createFields(): object
    {
        return {
            name: null,
            email: null,
            password: null,
            employee_id: null,
            is_admin: 0,
            permissions: []
        };
    }

    updateFields(model: IUser): object
    {
        return {
            id: model.id ?? null,
            name: model.name ?? null,
            email: model.email ?? null,
            password: null,
            employee_id: model.employee_id ?? null,
            is_admin: model.is_admin ?? 0,
            permissions: model.permissions ?? []
        };
    }

    get listOfPermission(): string
    {
        return this.permissions.map((permission) => permission.replace('_', ' ')).join(', ');
    }

    get listOfRole(): string
    {
        return this.roles.map((role) => role?.label?.replace('_', ' ')).join(', ');
    }

    static getColumns(): object
    {
        return [
            {
                title: 'ID',
                key: 'id',
                db_key: 'id',
                align: 'text-left',
                sortable: true,
                visible: false,
            },{
                title: 'Employee ID',
                key: 'employee_id',
                db_key: 'employee_id',
                align: 'text-left',
                sortable: true,
                visible: true,
            },{
                title: 'Name',
                key: 'name',
                db_key: 'name',
                align: 'text-left',
                sortable: true,
                visible: true,
            },{
                title: 'Role',
                key: 'listOfRole',
                db_key: 'roles',
                align: 'text-left',
                sortable: true,
                visible: true,
            },{
                title: 'Email',
                key: 'email',
                db_key: 'email',
                align: 'text-left',
                sortable: true,
                visible: true,
            },{
                title: 'Permissions',
                key: 'listOfPermission',
                db_key: 'permissions',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            
        ]
    }
}
