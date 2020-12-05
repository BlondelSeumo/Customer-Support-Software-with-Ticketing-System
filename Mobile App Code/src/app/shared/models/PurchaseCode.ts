import {User} from './User';

export class PurchaseCode {
    id: number;
    code: string;
    user_id: string;
    item_name: string;
    item_id: string;
    url?: string;
    image?: string;
    supported_until?: string;
    created_at?: string;
    updated_at?: string;
    user?: User;
    envato_username?: string;

    constructor(params: Object = {}) {
        for (const name in params) {
            this[name] = params[name];
        }
    }
}