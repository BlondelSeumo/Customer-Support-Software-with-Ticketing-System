export class SearchTerm {
    id: number;
    term: string;
    user_id: number;
    count: number;
    term_count?: number;
    created_at?: string;
    updated_at?: string;

    constructor(params: Object = {}) {
        for (const name in params) {
            this[name] = params[name];
        }
    }
}
