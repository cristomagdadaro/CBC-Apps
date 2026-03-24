import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoUser extends DtoBaseClass implements IUser {
    name: string;
    email: string;
    email_verified_at: Date | string | null;
    password: string;
    two_factor_secret: string;
    two_factor_recovery_codes: string;
    two_factor_confirmed_at: string;
    remember_token: string;
    current_team_id: string;
    profile_photo_path: string;
    is_admin: number | boolean;
    permissions: string[];
    employee_id: string | null;
    roles?: string[];

    constructor(data: IUser) {
        super(data);

        this.name = data?.name ?? '';
        this.email = data?.email ?? '';
        this.email_verified_at = data?.email_verified_at ?? null;
        this.password = data?.password ?? '';
        this.two_factor_secret = data?.two_factor_secret ?? '';
        this.two_factor_recovery_codes = data?.two_factor_recovery_codes ?? '';
        this.two_factor_confirmed_at = data?.two_factor_confirmed_at ?? '';
        this.remember_token = data?.remember_token ?? '';
        this.current_team_id = data?.current_team_id ?? '';
        this.profile_photo_path =  data?.profile_photo_path ?? '';
        this.is_admin = data?.is_admin ?? 0;
        this.permissions = data?.permissions ?? [];
        this.employee_id = data?.employee_id ?? null;
        this.roles = data?.roles ?? [];
    }
}
