import {User} from "./User";

export class UserDetails {
	id: number;
	details?: string;
	notes?: string;
	user_id: number;
	user?: User;

	constructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }
    }
}