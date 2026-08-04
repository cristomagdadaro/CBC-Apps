import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoGoLink extends DtoBaseClass implements IGoLink {
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

    constructor(data: Partial<IGoLink>) {
        super(data);

        this.slug = data?.slug ?? '';
        this.target_url = data?.target_url ?? '';
        this.clicks = Number(data?.clicks ?? 0);
        this.created = data?.created ?? '';
        this.expires = data?.expires ?? null;
        this.status = Boolean(data?.status);
        this.og_image = data?.og_image ?? null;
        this.og_title = data?.og_title ?? null;
        this.og_description = data?.og_description ?? null;
        this.qr_code = data?.qr_code ?? null;
        this.is_public = Boolean(data?.is_public);
        this.public_url = data?.public_url ?? '';
        this.is_expired = Boolean(data?.is_expired);
    }
}
