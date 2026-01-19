export default class ErrorResponse {
    /**
     * @param {Object} response
     */
    constructor(response) {
        this.title = response.statusText;
        this.message = response.data.message;
        this.errors = response.data.errors;
        this.status = response.status;
    }

    toObject() {
        return {
            title: this.title,
            message: this.message,
            errors: this.errors,
            status: this.status
        }
    }
}
