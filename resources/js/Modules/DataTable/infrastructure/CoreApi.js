import axios from "axios";
import BaseResponse from "@/Modules/DataTable/domain/BaseResponse";
import BaseModel from "@/Modules/DataTable/domain/BaseModel.js";
import ErrorResponse from "@/Modules/DataTable/domain/ErrorResponse.js";
import NotificationService from "@/Components/Notification/domain/NotificationService.js";

/** Application's Core JS Library to handle API request*/
export default class CoreApi{
    constructor(url) {
        /** Flag to check if the request is still processing*/
        this.processing = false;
        /** Base url of the api */
        this.baseUrl = url;
    }

    /** Set the base url of the api */
    setBaseUrl(url) {
        this.baseUrl = url;
    }

    /**
     * Get request to the api
     * @param {Object} params - parameters to be passed in the request
     * @param {Object} model - domain to be cast to
     * @returns {Promise<BaseResponse|*>}
     */
    async get(params, model = BaseModel)
    {
        this.processing = true;
        return await axios.get(this.baseUrl, { params: params })
            .then(response => {
                if (model !== BaseModel) {
                    response.data.data = this.castToModel(response.data.data, model);
                    return new BaseResponse(response);
                }
                return new BaseResponse(response);
            })
            .catch(error => {
                return this.determineError(error);
            })
            .finally(() => {
                this.processing = false;
            });
    }

    /**
     * Post request to the api
     * @param {Object} data - data to be passed in the request
     */
    async post(data){
        this.processing = true;
        return await axios.post(this.baseUrl, data)
            .then(response => {
                this.pushNotification(response);
                return new BaseResponse(response);
            })
            .catch(error => {
                return this.determineError(error);
            })
            .finally(() => {
                this.processing = false;
            });
    }

    /**
     * Put request to the api
     * @param {Object} data - data to be passed in the request
     */
    async put(data){
        this.processing = true;
        // check if the base url already contains the id
        if (this.baseUrl.includes(data.id))
            return await axios.put(this.baseUrl, data)
                .then(response => {
                    this.pushNotification(response);
                    return new BaseResponse(response);
                })
                .catch(error => {
                    return this.determineError(error);
                })
                .finally(() => {
                    this.processing = false;
                })

        return await axios.put(this.baseUrl + '/' + data.id, data)
            .then(response => {
                return new BaseResponse(response);
            })
            .catch(error => {
                return this.determineError(error);
            })
            .finally(() => {
                this.processing = false;
            });
    }

    /**
     * Delete request to the api
     * @param { string, Array } id - id to be passed in the request
     */
    async delete(id){
        this.processing = true;
        if (id instanceof Array && id.length > 1){
            return await axios.delete(this.baseUrl, {params: { ids: id }})
                .then(response => {
                    this.pushNotification(response);
                    return new BaseResponse(response);
                })
                .catch(error => {
                    return this.determineError(error);
                })
                .finally(() => {
                    this.processing = false;
                })
        }
        else
            return await axios.delete(this.baseUrl + '/' + id)
                .then(response => {
                    this.pushNotification(response);
                    return new BaseResponse(response);
                })
                .catch(error => {
                    return this.determineError(error);
                })
                .finally(() => {
                    this.processing = false;
                })
    }

    /**
     * Cast the data to the domain
     * @param {Object} data - data to be cast
     * @param {Object} model - domain to be cast to
     */
    castToModel(data, model) {
        return data.map(item => new model(item));
    }

    /**
     * Determine the error
     * @param {Object} error - error object
     */
    determineError(error) {
        const temp = new ErrorResponse(error.response);
        NotificationService.addNotification(temp);
        return temp;
    }

    pushNotification(response) {
        NotificationService.addNotification(new NotificationService(response));
    }
}
