import { ref } from "vue";
import BaseRequest from "@/Modules/domain/BaseRequest";
import BaseClass from "@/Modules/domain/BaseClass";

export default class CRCMDatatable {
    constructor(params = {}, model = BaseClass, apiAdapter = null) {
        this.api = apiAdapter;
        // array of columns to display
        this.columns = ref([]);
        this.columns = ref([]);
        // response from the server
        this.response = ref([]);
        // array of ids that are currently selected
        this.selected = ref([]);
        // class model that are current being handled in the CRMDatatable
        this.model = model;
        // array of ids to delete, can be multiple ids
        this.toDelete = ref([]);
        // when create or update, the modal will be forced to close after successful request
        this.closeAllModal = false;
        this._processing = false;
        this._errorBag = null;

        // retrieve params from local storage, if not found, create a new instance of BaseRequest
        // so that when the page is refreshed, the datatable will remember the last state
        const localParams = BaseRequest.getParamsLocal();
        this.request = localParams ? new BaseRequest({ ...localParams, ...params }) : new BaseRequest(params);
    }

    ensureApiAdapter() {
        const requiredMethods = ['get', 'post', 'put', 'delete'];
        const isValid = this.api && requiredMethods.every((method) => typeof this.api[method] === 'function');

        if (!isValid) {
            throw new Error('CRCMDatatable requires an API adapter with get/post/put/delete methods.');
        }
    }

    normalizeResponseShape(response) {
        if (!response || typeof response !== 'object') {
            return { data: [], meta: null };
        }

        if (response.meta && Array.isArray(response.data)) {
            return response;
        }

        const hasPaginatorFields =
            Array.isArray(response.data) &&
            Object.prototype.hasOwnProperty.call(response, 'current_page') &&
            Object.prototype.hasOwnProperty.call(response, 'last_page');

        if (!hasPaginatorFields) {
            return {
                ...response,
                data: Array.isArray(response.data) ? response.data : [],
                meta: response.meta || null,
            };
        }

        return {
            ...response,
            meta: {
                current_page: response.current_page,
                last_page: response.last_page,
                from: response.from,
                to: response.to,
                total: response.total,
                per_page: response.per_page,
            },
        };
    }

    async init() {
        this.ensureApiAdapter();
        this._processing = true;
        this._errorBag = null;

        try {
            const response = await this.api.get(this.request.toObject(), this.model);
            this.response = this.normalizeResponseShape(response);
            if (await this.checkForErrors(this.response))
                this.getColumnsFromResponse(this.response);
            this.closeAllModal = true;
        } catch (error) {
            this._errorBag = error?.response?.data || null;
            throw error;
        } finally {
            this._processing = false;
        }
    }

    async create(data) {
        this.ensureApiAdapter();
        const response = await this.api.post(this.model.toObject(data));
        if (await this.checkForErrors(response))
            await this.refresh();
    }

    async delete(id) {
        this.ensureApiAdapter();
        const response = await this.api.delete(id);
        if (await this.checkForErrors(response))
            await this.refresh();
        this.selected = this.selected.filter(item => item !== id);
    }

    async update(data) {
        this.ensureApiAdapter();
        const response = await this.api.put(this.model.toObject(data));
        if (await this.checkForErrors(response)) {
            await this.refresh();
            return true;
        }
        return false;
    }

    async deleteSelected() {
        this.ensureApiAdapter();
        const selectedIds = Array.isArray(this.selected) ? [...this.selected] : [];
        if (!selectedIds.length) {
            return;
        }

        let response = null;
        if (typeof this.api.deleteMany === 'function') {
            response = await this.api.deleteMany(selectedIds);
        } else {
            response = await Promise.all(selectedIds.map((id) => this.api.delete(id)));
        }

        if (await this.checkForErrors(response)) {
            await this.refresh();
        }
        this.selected = [];
    }

    async checkForErrors(response) {
        if (response) {
            this.closeAllModal = true;
            return true;
        }
        return false;
    }

    get errorBag() {
        return this._errorBag;
    }

    set errorBag(value) {
        this._errorBag = value;
    }

    get processing() {
        return this._processing;
    }

    set processing(value) {
        this._processing = value;
    }

    async refresh() {
        await this.init();
    }

    async nextPage() {
        if (this.response['meta']['current_page'] + 1 <= this.response['meta']['last_page'])
            this.request.updateParam('page', this.response['meta']['current_page'] + 1);
        await this.refresh();
    }

    async prevPage() {
        this.request.updateParam('page', this.response['meta']['current_page'] - 1);
        await this.refresh();
    }

    async firstPage() {
        this.request.updateParam('page', 1);
        await this.refresh();
    }

    async lastPage() {
        this.request.updateParam('page', this.response['meta']['last_page']);
        await this.refresh();
    }

    async gotoPage(page) {
        this.request.updateParam('page', page);
        await this.refresh();
    }

    async sortFunc(params) {
        this.request.updateParam('sort', params.sort);
        this.request.updateParam('order', this.request.getParam('order') === 'asc' ? 'desc' : 'asc');
        await this.refresh();
    }

    filterByColumn(params) {
        this.request.updateParam('filter', params.column);
    }

    isExactFilter(params) {
        this.request.updateParam('is_exact', params.is_exact);
    }

    async searchFunc(params) {
        this.request.updateParam('search', params.search);
        await this.refresh();
    }

    async perPageFunc(params) {
        this.request.updateParam('per_page', params.per_page);
        if (this.response['meta'] && this.response['meta']['last_page'] === this.response['meta']['current_page'])
            // if the current page is the last page, set the page to the last page
            this.request.updateParam('page', this.response['meta']['last_page']);

        await this.refresh();
    }

    async scopeBy(params) {
        this.request.updateParam('scope_by', params.scope_by);
        await this.refresh();
    }

    addSelected(id) {
        if (!this.isSelected(id))
            this.selected.push(id);
        else
            this.removeSelected(id);
    }

    removeSelected(id) {
        this.selected = this.selected.filter(item => item !== id);
    }

    isSelected(id) {
        return this.selected.includes(id);
    }

    selectAll() {
        //prevent duplicates
        this.selected = [...new Set([...this.selected, ...this.response['data'].map(item => item.id)])];
    }

    deselectAll() {
        this.selected = [];
    }

    async exportCSV() {
        this.ensureApiAdapter();
        let link = document.createElement("a");
        const selectedIds = Array.isArray(this.selected) ? [...this.selected] : [];
        const selectedIdSet = new Set(selectedIds);
        const originalPerPage = this.request.getParam('per_page');
        try {
            // Update per_page parameter to fetch all data in one request
            this.request.updateParam('per_page', this.response?.meta?.total || this.response?.data?.length || 10);

            // Fetch data from API
            let response = this.normalizeResponseShape(await this.api.get(this.request.toObject(), this.model));
            let data = response.data;

            // If rows are selected, export should prioritize selected rows over active filters.
            if (selectedIdSet.size > 0) {
                data = data.filter((row) => selectedIdSet.has(row.id));
            }

            // Filter and map visible columns
            let columnsTitles = this.model.visibleColumns()
                .map(column => column.title);
            let columnKeys = this.model.visibleColumns()
                .map(column => column.key);
            // Prepare CSV content
            let csvContent = "data:text/csv;charset=utf-8,";

            // Add header row
            csvContent += columnsTitles.join(",") + "\r\n";
            // Add data rows
            data.forEach(function (rowArray) {
                let row = columnKeys.map(column => {
                    let value = BaseClass.getNestedValue(rowArray, column);
                    // Check if the value contains a comma
                    if (typeof value === 'string' && value.includes(',')) {
                        // Encapsulate the value in double quotes
                        return `"${value}"`;
                    }
                    return value;
                }).join(",");
                csvContent += row + "\r\n";
            });


            // Encode CSV content
            let encodedUri = encodeURI(csvContent);

            // Create download link
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", `${new Date().toISOString().replace(/:/g, "-").replace(/\..+/, '')}.csv`);

            // Append link to body and trigger download
            document.body.appendChild(link);
            link.click();
        } catch (error) {

        } finally {
            if (originalPerPage === undefined || originalPerPage === null) {
                this.request.removeParam('per_page');
            } else {
                this.request.updateParam('per_page', originalPerPage);
            }

            // Clean up: remove link from body
            if (link) {
                document.body.removeChild(link);
            }
        }
    }
    async importCSV(data) {
        this.ensureApiAdapter();
        let success = 0;
        let failed = 0;
        let total = 0;
        for (const row of data) {
            total++;
            try {
                const response = await this.api.post(this.model.toObject(row));
                if (response) {
                    success++;
                } else {
                    failed++;
                }
            } catch (e) {
                failed++;
            }
        }

        if (success === total)
            console.log('All rows imported successfully');

        else if (failed > 0 && success > 0 && success < total)
            console.warn(`Imported ${success} rows successfully, but failed to import ${failed} rows`);

        else if (failed === total)
            console.error('Failed to import all rows');

        if (failed <= 0)
            await this.refresh();
    }

    getColumnsFromResponse(response) {
        if (response['data'] && response['data'].length > 0) {
            this.columns = Object.keys(response['data'][0]);
            // only include columns that are visible
            this.columns = this.model.getColumns()
                .filter(column => column.visible !== false)
                .map(column => ({
                    ...column,
                    visible: column.visible ?? true
                }));

            this.columns = this.formatColumns(this.columns);

            // store columns in the local storage with the current url as key
            localStorage.setItem(window.location.pathname, JSON.stringify(this.columns));
        }
        else if (localStorage.getItem(window.location.pathname))
            this.columns = JSON.parse(localStorage.getItem(window.location.pathname)
            );
    }

    formatColumnName = (columnName) => {
        return columnName.replace(/_/g, ' ').replace(/\w\S*/g, (w) => (w.replace(/^\w/, (c) => c.toUpperCase())));
    }

    formatColumns = (columns) => {
        return columns.map(column => {
            return { name: column.db_key, label: column.title, sortable: true }
        });
    }
}
