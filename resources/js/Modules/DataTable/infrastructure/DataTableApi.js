import CoreApi from "@/Modules/DataTable/infrastructure/CoreApi.js";
import BaseResponse from "@/Modules/DataTable/domain/BaseResponse.js";
import BaseModel from "@/Modules/DataTable/domain/BaseModel.js";
import BaseRequest from "@/Modules/DataTable/domain/BaseRequest.js";
import {ValidationErrorResponse} from "@/Modules/core/infrastructure/ValidationErrorResponse.js";

export default class DataTableApi{
    constructor(baseUrl, model = BaseModel, params = {}) {
        /** Core api service instance */
        this.api = new CoreApi(baseUrl);
        /** Model of the data table */
        this.model = model;
        /** Server raw response*/
        this.response = new BaseResponse();
        /** Flag to close all modals*/
        this.closeAllModal = false;
        /** Errors container*/
        this.errorBag = [];
        /** Selected Rows*/
        this.selected = [];

        // retrieve params from local storage, if not found, create a new instance of BaseRequest
        // so that when the page is refreshed, the datatable will remember the last state
        //const localParams = BaseRequest.getParamsLocal();
        //this.request = localParams? new BaseRequest(localParams) : new BaseRequest(params);
        this.request = new BaseRequest(params);
    }

    /** Initialize the data table*/
    async init() {
        try {
            this.processing = true;
            this.response = await this.api.get(this.request.toObject(), this.model);
            this.processing = false;
        } catch (error) {
            throw new Error(error);
        }
    }

    /** Refresh the data table*/
    async refresh() {
        await this.init();
        this.closeAllModal = true;
    }

    /** Go to the given page*/
    async goToPage(page) {
        this.request.updateParam('page', page);
        await this.refresh();
    }

    /** Move to the next page*/
    async nextPage() {
        if(this.response.meta?.current_page + 1 <= this.response.meta?.last_page)
            this.request.updateParam('page', this.response.meta.current_page + 1);
        await this.refresh();
    }

    /** Move to the previous page*/
    async prevPage() {
        this.request.updateParam('page', this.response.meta?.current_page - 1);
        await this.refresh();
    }

    /** Move to the first page*/
    async firstPage() {
        this.request.updateParam('page', 1);
        await this.refresh();
    }

    /** Move to the last page*/
    async lastPage() {
        this.request.updateParam('page', this.response.meta?.last_page);
        await this.refresh();
    }

    /**
     * Sort the data table
     * @param {Object} params - sort parameters
     */
    async sortFunc(params) {
        this.request.updateParam('sort', params.sort);
        this.request.updateParam('order', this.request.getParam('order') === 'asc' ? 'desc' : 'asc');
        await this.refresh();
    }

    /**
     * Filter the data table
     * @param {Object} params - filter parameters
     */
    filterByColumn(params) {
        this.request.updateParam('filter', params.column);
    }

    /**
     * Flag to search the exact string in the data table
     * @param {Object} params - search parameters
     */
    isExactFilter(params) {
        this.request.updateParam('is_exact', params.is_exact);
    }

    /**
     * Search a string in the data table
     * @param {Object} params - search parameters
     */
    async searchFunc(params) {
        this.request.updateParam('search', params.search);
        await this.refresh();
    }

    /**
     * Change the number of items per page
     * @param {Object} params - number of items per page
     */
    async perPageFunc(params){
        this.request.updateParam('per_page', params.per_page)
        if (this.response.meta?.last_page === this.response.meta?.current_page)
            // if the current page is the last page, set the page to the last page
            this.request.updateParam('page', this.response.meta?.last_page);
        await this.refresh();
    }

    /**
     * Delete the selected items
     * @param {Array} ids - ids of the selected items
     */
    addSelected(ids) {
        if(!this.isSelected(ids))
            this.selected.push(ids);
        else
            this.removeSelected(ids);
    }

    /**
     * Remove the selected item
     * @param {Number, Array} ids - id of the selected item
     */
    removeSelected(ids) {
        this.selected = this.selected.filter(item => item !== ids);
    }

    /**
     * Check if the item is selected
     * @param {Array} id
     * @returns {boolean}
     */
    isSelected(id) {
        if (this.selected.length > 0 && id)
            return this.selected.includes(id);
    }

    /** Select all items */
    selectAll() {
        const rows = this.response.data ?? [];
        this.selected = [...new Set([...this.selected, ...rows.map(item => item.id)])];
    }

    /** Deselect all items */
    deselectAll() {
        this.selected = [];
    }

    /** Export data*/
    exportCSV() {
        console.log('Exporting data');
    }

    /** Import data*/
    importCSV() {
        console.log('Importing data');
    }

    /**
     * Insert new data
     */
    async create(data) {
        this.processing = true;
        const response = await this.api.post(this.model.toObject(data));

        if (response instanceof ValidationErrorResponse){
            this.errorBag = response;
            this.processing = false;
            return;
        }

        this.processing = false;
        await this.refresh();
        this.errorBag = [];
    }

    /**
     * Update data
     */
    async update(data) {
        this.processing = true;
        const response = await this.api.put(this.model.toObject(data));

        if (response instanceof ValidationErrorResponse){
            this.errorBag = response;
            this.processing = false;
            return;
        }

        this.processing = false;
        await this.refresh();
        this.errorBag = [];
    }

    /**
     * Delete data
     */
    async delete(id) {
        this.processing = true;
        const response = await this.api.delete(id);

        if (response instanceof ValidationErrorResponse){
            this.errorBag = response;
            this.processing = false;
            return;
        }
        this.selected = [];
        this.processing = false;
        await this.refresh();
        this.errorBag = [];
    }
}
