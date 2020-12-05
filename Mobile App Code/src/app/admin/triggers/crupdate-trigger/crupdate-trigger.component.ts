import {ChangeDetectorRef, Component, OnInit, ViewEncapsulation} from '@angular/core';
import {Location} from '@angular/common';
import {TriggersService} from '../triggers.service';
import {Condition} from '../../../shared/models/Condition';
import {Action} from '../../../shared/models/Action';
import {ActivatedRoute} from '@angular/router';
import {Trigger} from '../../../shared/models/Trigger';
import {Toast} from '@common/core/ui/toast.service';
import {ucFirst} from '@common/core/utils/uc-first';
import {finalize} from 'rxjs/operators';
import {BehaviorSubject} from 'rxjs';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';

@Component({
    selector: 'crupdate-trigger',
    templateUrl: './crupdate-trigger.component.html',
    styleUrls: ['./crupdate-trigger.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class CrupdateTriggerComponent implements OnInit {
    public editing = false;
    public loading = false;
    public triggerModel = {conditions: [], actions: []};
    public errors = {};
    public allConditions$ = new BehaviorSubject<Condition[]>([]);
    public allActions$ = new BehaviorSubject<Action[]>([]);
    public valueOptions = {};

    constructor(
        protected location: Location,
        protected trigger: TriggersService,
        private route: ActivatedRoute,
        private toast: Toast,
        private cd: ChangeDetectorRef,
    ) {}

    ngOnInit() {
        this.trigger.getConditions().subscribe(conditions => this.allConditions$.next(conditions));
        this.trigger.getActions().subscribe(actions => this.allActions$.next(actions));
        this.hydrateModelWithPlaceholders();

        this.route.params.subscribe(params => {
            if (params['id']) {
                this.editing = true;
                this.hydrateModel();
            }
        });
    }

    public createOrUpdateTrigger() {
        const action = this.editing ? 'update' : 'create';
        this.loading = true;

        this.trigger[action](this.getPayload())
            .pipe(finalize(() => this.loading = false))
            .subscribe(() => {
                this.errors = {};
                this.toast.open(`${ucFirst(action)}d a Trigger`);
                this.goBack();
            }, (errResponse: BackendErrorResponse) => this.errors = errResponse.errors);
    }

    public addCondition(type: string) {
        this.triggerModel.conditions.push({condition_id: 0, matchType: type});
    }

    public addAction() {
        this.triggerModel.actions.push({action_id: 0, value: {}});
    }

    public removeAction(action: Object) {
        // if there is only one action, then simply reset it to default state
        if (this.triggerModel.actions.length === 1) {
            this.triggerModel.actions[0] = {action_id: 0, value: {}};

        // if there is more then one action then remove specified one
        } else {
            const index = this.triggerModel.actions.findIndex((currentAction: Object) => {
                return currentAction === action;
            });

            this.triggerModel.actions.splice(index, 1);
        }
    }

    public goBack() {
        this.triggerModel = {conditions: [], actions: []};
        this.location.back();
    }

    public onActionSelect(selectedAction: {[key: string]: any}) {
        selectedAction.actionModel = this.getActionById(selectedAction.action_id);

        const inputs = selectedAction['actionModel']['input_config']['inputs'];

        // clear input model of previous values
        selectedAction['value'] = {};

        // if there are not inputs for this action, bail
        if ( ! inputs || ! inputs[0]) return;

        // create an object property for each input
        inputs.forEach(input => {
            selectedAction['value'][input['name']] = null;
        });

        // if input is a select, we'll need to do some extra work
        if (inputs[0]['select_options']) {
            this.handleSelectOptions(selectedAction);
        }
    }

    private getActionById(id: number|string) {
        return this.allActions$.value.find(action => action.id === id);
    }

    protected getPayload(): Object {
        return {
            id: this.triggerModel['id'],
            name: this.triggerModel['name'],
            description: this.triggerModel['description'],
            actions: this.triggerModel.actions.slice().filter(action => action.action_id !== 0),
            conditions: this.triggerModel.conditions.slice().filter(condition => condition.condition_id !== 0),
        };
    }

    private hydrateModel() {
        this.route.data.subscribe(data => {
            this.mapBackendTrigger(data['trigger']);
        });
    }

    private mapBackendTrigger(trigger: Trigger) {
        this.triggerModel['id'] = trigger.id;
        this.triggerModel['name'] = trigger.name;
        this.triggerModel['description'] = trigger.description;

        this.triggerModel.conditions = [];
        this.triggerModel.actions = [];

        trigger.conditions.forEach(condition => {
            this.triggerModel.conditions.push({
                conditionModel: condition,
                condition_id: condition.id,
                operator_id: condition['pivot']['operator_id'],
                operatorModel: condition['selected_operator'],
                matchType: condition['pivot']['match_type'],
                value: condition['pivot']['condition_value']
            });
        });

        trigger.actions.forEach(action => {
            const viewAction = {
                actionModel: action,
                action_id: action.id,
                value: JSON.parse(action['pivot']['action_value'])
            };
            // load select options from backend, if needed
            if (viewAction.actionModel.input_config['inputs'].length && viewAction.actionModel.input_config['inputs'][0]['select_options']) {
                this.handleSelectOptions(viewAction);
            }
            this.triggerModel.actions.push(viewAction);
        });

        this.hydrateModelWithPlaceholders();
    }

    protected hydrateModelWithPlaceholders() {
        // add one placeholder condition for each match type if there are none yet
        ['all', 'any'].forEach(type => {
            if ( ! this.triggerModel.conditions.filter(condition => condition.matchType === type).length) {
                this.addCondition(type);
            }
        });

        // add one placeholder for action if there are none yet
        if ( ! this.triggerModel.actions.length) {
            this.addAction();
        }
    }

    protected handleSelectOptions(action: object) {
        const input = action['actionModel']['input_config']['inputs'][0],
            optionsName = input['select_options'],
            inputName   = input['name'],
            actionValue = action['value'][inputName];

        // if we've already fetched options for this value,
        // set default one on specified action model and bail
        if (this.valueOptions[optionsName]) {
            action['value'][inputName] = this.valueOptions[optionsName].find(data => {
                return actionValue ? data.value === +actionValue : true;
            }).value;
            return;
        }

        // fetch and cache select value options by specified name
        this.trigger.getValueOptions(optionsName).subscribe(response => {
            this.valueOptions[optionsName] = response['data'];
            action['value'][inputName] = this.valueOptions[optionsName].find(data => {
                // value could be integer as string "1" or a string "open"
                const actionVal = +actionValue || actionValue;
                return actionValue ? data.value === actionVal : true;
            }).value;
            this.cd.markForCheck();
        });
    }
}
