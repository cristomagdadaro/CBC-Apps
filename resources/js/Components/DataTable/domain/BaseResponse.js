export default class BaseResponse {

    /**
     * @param {Object} response
     */
    constructor(response = {}) {
        this.data = response.data ? response.data.data : response.data;
        this.meta = response.data ? response.data.meta : response.data;
        this.links = response.data ? response.data.links : response.data;

        this.error = response.error;
        this.status = response.status;
    }

    toObject() {
        return {
            data: this.data,
            meta: this.meta,
            links: this.links
        }
    }
}
