export class Operator {
	id: number;
	name: string;
	display_name: string;
	type: string = 'primitive';
	value_type: string = 'text';
	value_placeholder?: string;
	validation_rules?: string;

	constructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }
    }
}