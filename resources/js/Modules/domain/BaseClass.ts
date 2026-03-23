import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import IBaseClass from "@/Modules/interface/IBaseClass";

export default class BaseClass extends DtoBaseClass implements IBaseClass
{
    _indexUri?: string;
    _showUri?: string;
    _storeUri?: string;
    _updateUri?: string;
    _destroyUri?: string;
    _multiDestroyUri?: string;
    _dataViewUri?: string;
    _summaryUri?: string;

    _appendedWith?: string[];
    _appendedCount?: string[];
    _titles?: { [key: string]: string };

    constructor(resp = {}) {
        super(resp);
    }
    set indexUri(value: string) {
        this._indexUri = value;
    }

    set showUri(value: string) {
        this._showUri = value;
    }

    set storeUri(value: string) {
        this._storeUri = value;
    }

    set updateUri(value: string) {
        this._updateUri = value;
    }

    set destroyUri(value: string) {
        this._destroyUri = value;
    }

    set dataViewUri(value: string) {
        this._dataViewUri = value;
    }

    set multiDestroyUri(value: string) {
        this._multiDestroyUri = value;
    }

    set summaryUri(value: string) {
        this._summaryUri = value;
    }

    set appendWith(value: string[]) {
        this._appendedWith = value;
    }

    set appendCount(value: string[]) {
        this._appendedCount = value;
    }

    get dataViewUri(): string {
        return this._dataViewUri;
    }

    get indexUri(): string {
        return this._indexUri;
    }

    get showUri(): string {
        return this._showUri;
    }

    get storeUri(): string {
        return this._storeUri;
    }

    get updateUri(): string {
        return this._updateUri;
    }

    get destroyUri(): string {
        return this._destroyUri;
    }

    get multiDestroyUri(): string {
        return this._multiDestroyUri;
    }

    get summaryUri(): string {
        return this._summaryUri;
    }

    get appendWith(): string[] {
        return this._appendedWith;
    }

    get appendCount(): string[] {
        return this._appendedCount;
    }

    static get appendWith(): string[] {
        return new this({})._appendedWith;
    }

    static get appendCount(): string[] {
        return new this({})._appendedCount;
    }

    static get indexUri(): string {
        return new this({})._indexUri;
    }

    static get showUri() {
        return new this({})._showUri;
    }

    static get storeUri() {
        return new this({})._storeUri;
    }
    static get updateUri() {
        return new this({})._updateUri;
    }

    static get destroyUri() {
        return new this({})._destroyUri;
    }

    static get multiDestroyUri() {
        return new this({})._multiDestroyUri;
    }

    static get summaryUri() {
        return new this({})._summaryUri;
    }
}
