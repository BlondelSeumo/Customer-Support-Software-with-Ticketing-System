import {User} from "./User";

export class Email {
	id: number;
	address: string;
	user_id: number;
	user?: User;

	constructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }
    }
}