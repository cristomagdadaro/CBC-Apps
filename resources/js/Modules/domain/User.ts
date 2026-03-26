import DtoUser from "@/Modules/dto/DtoUser";

export default class User extends DtoUser {
    static endpoints = {
        index: 'api.users.index',
        post: 'api.users.store',
        put: 'api.users.update',
        delete: 'api.users.destroy',
        create: 'system.users.create',
        show: 'system.users.show',
    };

    constructor(response: DtoUser) {
        super(response);

        this.api._apiIndex = User.endpoints.index;
        this.api._apiPost = User.endpoints.post;
        this.api._apiPut = User.endpoints.put;
        this.api._apiDelete = User.endpoints.delete;
        
        this.createPage = User.endpoints.create;
        this.showPage = User.endpoints.show;

        this.api.appendWith = ['roles'];
    }

    createFields(): object
    {
        return {
            name: null,
            email: null,
            password: null,
            password_confirmation: null,
            employee_id: null,
            is_admin: 0,
            roles: [],
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
            password_confirmation: null,
            employee_id: model.employee_id ?? null,
            is_admin: model.is_admin ?? 0,
            roles: Array.isArray(model.roles)
                ? model.roles.map((role: any) => typeof role === 'string' ? role : role?.name).filter(Boolean)
                : [],
            permissions: model.permissions ?? []
        };
    }

    get listOfPermission(): string
    {
        return this.permissions.map((permission) => permission.replace('_', ' ')).join(', ');
    }

    get listOfRole(): string
    {
        return this.roles
            .map((role: any) => (typeof role === 'string' ? role : role?.label || role?.name || ''))
            .filter(Boolean)
            .map((role: string) => role.replace('_', ' '))
            .join(', ');
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
