interface IGoLink extends IBaseClass {
    id: string;
    slug: string;
    target_url: string;
    clicks: number;
    created: string;
    expires: string | null;
    status: boolean;
    og_image: string | null;
    og_title: string | null;
    og_description: string | null;
    qr_code: string | null;
    is_public: boolean;
    public_url: string;
    is_expired: boolean;
}
