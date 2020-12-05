import {Condition} from "./Condition";
import {Action} from "./Action";

export class Trigger {
	id: number;
	name: string;
	description?: string;
	times_fired: number;
	created_at?: string;
	updated_at?: string;
	conditions?: Condition[];
	actions?: Action[];

	constructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }
    }
}