interface IUser extends IBaseClass {
    name: string;
    email: string;
    email_verified_at: Date;
    password: string;
    two_factor_secret: string;
    two_factor_recovery_codes: string;
    two_factor_confirmed_at: string;
    remember_token: string;
    current_team_id: string;
    profile_photo_path: string;
}
