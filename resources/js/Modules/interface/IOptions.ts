export default interface IOptions extends IBaseClass {
    key: string;
    label: string;
    description?: string;
    type: string;
    group?: string;
    value?: any;
    options?: any;
}
