import {Operator} from './Operator';

export class Condition {
    id: number;
    name: string;
    type: string;
    operators?: Operator[];
    input_config?: {
        type: 'select'|'textarea'|'text';
        operators: string[];
        static?: boolean;
        select_options?: 'string'|{name: string, value: string|number}[],
        default_value?: string|number;
        placeholder?: string;
    }[];

    constructor(params: Object = {}) {
        for (const name in params) {
            this[name] = params[name];
        }
    }
}
