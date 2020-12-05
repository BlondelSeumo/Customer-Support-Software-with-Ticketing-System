import {Condition} from '../../../shared/models/Condition';
import {Operator} from '../../../shared/models/Operator';

export interface CrupdateTriggerConditionModel {
    condition_id: number;
    matchType: 'any'|'all';
    conditionModel?: Condition;
    operator_id?: number;
    operatorModel?: Operator;
    value?: string;
}
