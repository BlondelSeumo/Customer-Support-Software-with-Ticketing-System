export class Setting {
	id: number;
	name: string;
	value: string;
	private: boolean;
	created_at?: string;
	updated_at?: string;

	constructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }
    }
}