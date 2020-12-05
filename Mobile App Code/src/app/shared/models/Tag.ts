import {Category} from './Category';

export class Tag {
    id: number;
    name: string;
    display_name: string;
    type = 'custom';
    created_at?: string;
    updated_at?: string;
    tickets_count?: number;
    categories?: Category[];

    constructor(params: Object = {}) {
        for (const name in params) {
            this[name] = params[name];
        }
    }
}
