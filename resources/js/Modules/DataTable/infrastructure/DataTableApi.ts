import ApiMixin from '@/Modules/mixins/ApiMixin';

const apiMixinMethods: Record<string, any> = (ApiMixin.methods || {}) as Record<string, any>;

export default class DataTableApi {
    baseUrl: string | null;
    model: any;
    params: Record<string, any>;
    processing: boolean;
    response: any;
    errorBag: any[];
    selected: Array<string | number>;

    constructor(baseUrl: string | null = null, model: any = null, params: Record<string, any> = {}) {
        this.baseUrl = baseUrl;
        this.model = model;
        this.params = { ...params };
        this.processing = false;
        this.response = null;
        this.errorBag = [];
        this.selected = [];
    }

    setModel(model: any): void {
        this.model = model;
    }

    setBaseUrl(baseUrl: string | null): void {
        this.baseUrl = baseUrl;
    }

    setParams(params: Record<string, any> = {}): void {
        this.params = { ...params };
    }

    mergeParams(params: Record<string, any> = {}): Record<string, any> {
        return {
            ...this.params,
            ...params,
        };
    }

    resolveModel(modelOverride: any = null): any {
        const source = modelOverride || this.model;

        if (!source) {
            return null;
        }

        const instance = typeof source === 'function' ? new source() : source;

        if (this.baseUrl && instance?.api) {
            instance.api.apiIndex = this.baseUrl;
        }

        return instance;
    }

    createFormContext(payload: Record<string, any> = {}) {
        return {
            data: () => payload,
            clearErrors: () => undefined,
            setError: () => undefined,
            reset: () => undefined,
        };
    }

    createMixinContext(modelOverride: any = null, payload: Record<string, any> = {}) {
        const model = this.resolveModel(modelOverride);

        return {
            model,
            form: this.createFormContext(payload),
            processing: false,
            toDelete: null,
            confirmDelete: false,
            resetForm: () => undefined,
            setFormAction: () => undefined,
        };
    }

    async getIndex(params: Record<string, any> = {}, modelOverride: any = null): Promise<any> {
        const payload = this.mergeParams(params);
        const context = this.createMixinContext(modelOverride, payload);

        if (!context.model?.api || typeof apiMixinMethods.fetchData !== 'function') {
            throw new Error('DataTableApi requires a model with an index api.');
        }

        try {
            this.processing = true;
            const response = await apiMixinMethods.fetchData.call(context);
            this.response = response;
            return response;
        } finally {
            this.processing = false;
        }
    }

    getDeleteIdentifier(payload: any): string | number | null {
        if (!payload) {
            return null;
        }

        if (payload.id != null) {
            return payload.id;
        }

        if (typeof payload.identifier === 'function') {
            return payload.identifier()?.id ?? null;
        }

        return payload;
    }

    async create(payload: Record<string, any>, modelOverride: any = null): Promise<any> {
        const context = this.createMixinContext(modelOverride);
        const routeName = context.model?.api?._apiPost;

        if (!routeName || typeof apiMixinMethods.fetchPostApi !== 'function') {
            throw new Error('DataTableApi requires a model with a create api.');
        }

        try {
            this.processing = true;
            return await apiMixinMethods.fetchPostApi.call(context, routeName, payload);
        } finally {
            this.processing = false;
        }
    }

    async update(payload: Record<string, any>, modelOverride: any = null): Promise<any> {
        const context = this.createMixinContext(modelOverride);
        const routeName = context.model?.api?._apiPut;
        const identifier = this.getDeleteIdentifier(payload);

        if (!routeName || typeof apiMixinMethods.fetchPutApi !== 'function') {
            throw new Error('DataTableApi requires a model with an update api.');
        }

        try {
            this.processing = true;
            return await apiMixinMethods.fetchPutApi.call(context, routeName, identifier, payload);
        } finally {
            this.processing = false;
        }
    }

    async delete(payload: any, modelOverride: any = null): Promise<any> {
        const context = this.createMixinContext(modelOverride);
        const routeName = context.model?.api?._apiDelete;
        const identifier = this.getDeleteIdentifier(payload);

        if (!routeName || typeof apiMixinMethods.fetchDeleteApi !== 'function') {
            throw new Error('DataTableApi requires a model with a delete api.');
        }

        try {
            this.processing = true;
            return await apiMixinMethods.fetchDeleteApi.call(context, routeName, identifier);
        } finally {
            this.processing = false;
        }
    }

    async exportCSV(data: Array<any>, filename: string | null = null, modelOverride: any = null): Promise<any> {
        const context = this.createMixinContext(modelOverride);

        if (typeof apiMixinMethods.exportCSV !== 'function') {
            return null;
        }

        return await apiMixinMethods.exportCSV.call(context, data, filename);
    }

    addSelected(id: string | number): void {
        if (!this.isSelected(id)) {
            this.selected.push(id);
            return;
        }

        this.removeSelected(id);
    }

    removeSelected(id: string | number): void {
        this.selected = this.selected.filter((item) => item !== id);
    }

    isSelected(id: string | number): boolean {
        return this.selected.includes(id);
    }

    selectAll(ids: Array<string | number>): void {
        this.selected = Array.from(new Set([...(this.selected || []), ...(ids || [])]));
    }

    deselectAll(): void {
        this.selected = [];
    }
}
