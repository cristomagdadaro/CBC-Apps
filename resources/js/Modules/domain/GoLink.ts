import DtoGoLink from "@/Modules/dto/DtoGoLink";

export default class GoLink extends DtoGoLink {
    static endpoints = {
        index: 'api.golinks.index',
        post: 'api.golinks.store',
        put: 'api.golinks.update',
        delete: 'api.golinks.destroy',
        show: 'golinks.show',
        create: 'golinks.create',
    };

    constructor(response: Partial<IGoLink> = {}) {
        super(response);

        this.api._apiIndex = GoLink.endpoints.index;
        this.api._apiPost = GoLink.endpoints.post;
        this.api._apiPut = GoLink.endpoints.put;
        this.api._apiDelete = GoLink.endpoints.delete;

        this.showPage = GoLink.endpoints.show;
        this.createPage = GoLink.endpoints.create;
    }

    createFields(): object {
        return {
            slug: null,
            target_url: null,
            expires: null,
            status: true,
            og_title: null,
            og_description: null,
            og_image: null,
            is_public: false,
        };
    }

    updateFields(data: IGoLink): object {
        return {
            id: data?.id,
            slug: data?.slug ?? null,
            target_url: data?.target_url ?? null,
            expires: data?.expires ?? null,
            status: Boolean(data?.status),
            og_title: data?.og_title ?? null,
            og_description: data?.og_description ?? null,
            og_image: data?.og_image ?? null,
            is_public: Boolean(data?.is_public),
        };
    }

    static getColumns() {
        return [
            {
                title: 'ID',
                key: 'id',
                db_key: 'id',
                align: 'text-center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Slug',
                key: 'slug',
                db_key: 'slug',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Go Link',
                key: 'public_url',
                db_key: 'slug',
                align: 'text-left',
                sortable: false,
                visible: true,
            },
            {
                title: 'Target URL',
                key: 'target_url',
                db_key: 'target_url',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Clicks',
                key: 'clicks',
                db_key: 'clicks',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Status',
                key: 'status',
                db_key: 'status',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Expires',
                key: 'expires',
                db_key: 'expires',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Public Submission',
                key: 'is_public',
                db_key: 'is_public',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Created',
                key: 'created',
                db_key: 'created',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
        ];
    }
}
