import {Component, Input, ViewEncapsulation} from '@angular/core';
import {Condition} from '../../../shared/models/Condition';
import {CrupdateTriggerConditionModel} from '../crupdate-trigger/crupdate-trigger-model';

@Component({
    selector: 'conditions',
    templateUrl: './conditions.component.html',
    styleUrls: ['./conditions.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class ConditionsComponent {

    /**
     * Model this condition should be attached to.
     */
    @Input() public model: CrupdateTriggerConditionModel[];

    @Input() public matchType: 'all'|'any';

    /**
     * List of available conditions for triggers.
     */
    @Input() public allConditions: Condition[] = [];

    /**
     * Backend side errors.
     */
    @Input() public errors = {};

    /**
     * Remove specified condition form conditions model array.
     */
    public removeCondition(condition: CrupdateTriggerConditionModel) {
        const conditions = this.model.filter(c => c.matchType === this.matchType);

        const index = this.model.findIndex(c => c === condition);

        // if there is only one condition, then simply reset it to default state
        if (conditions.length === 1) {
            this.model[index] = {condition_id: 0, matchType: this.matchType};

        // if there are more then one condition then remove specified one
        } else {
            this.model.splice(index, 1);
        }
    }

    /**
     * Fired when new condition is selected.
     */
    public onConditionSelect(selectedCondition: CrupdateTriggerConditionModel) {
        // find condition model with matching id
        const conditionModel = this.allConditions.find(condition => condition.id === selectedCondition.condition_id);

        // attach condition model to condition that was just selected
        selectedCondition.conditionModel = conditionModel;

        // attach id of first operator attached to selected condition model
        selectedCondition.operator_id = conditionModel.operators[0].id;

        // attach first operator model attached to selected condition model
        selectedCondition.operatorModel = conditionModel.operators[0];

        // clear previous values attached to selected condition
        selectedCondition.value = conditionModel.type === 'event:type' ? 'ticket_created' : '';
    }

    public onOperatorSelect(selectedCondition: CrupdateTriggerConditionModel) {
        const conditionModel = this.allConditions.find(condition => condition.id === selectedCondition.condition_id);
        selectedCondition.operatorModel = conditionModel.operators.find(o => o.id === selectedCondition.operator_id);
    }
}
