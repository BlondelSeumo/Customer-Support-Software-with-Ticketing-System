import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {AppearanceEditor} from '@common/admin/appearance/appearance-editor/appearance-editor.service';
import {FormArray, FormBuilder, FormControl, FormGroup} from '@angular/forms';
import {Settings} from '@common/core/config/settings.service';
import {NewTicketPageContent} from './new-ticket-page-content';

const CONFIG_KEY = 'hc.newTicket.appearance';

@Component({
    selector: 'new-ticket-appearance-panel',
    templateUrl: './new-ticket-appearance-panel.component.html',
    styleUrls: ['./new-ticket-appearance-panel.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class NewTicketAppearancePanelComponent implements OnInit {
    public defaultValues: NewTicketPageContent;
    public form = this.fb.group({
        title: [''],
        categoryLabel: [''],
        subjectLabel: [''],
        descriptionLabel: [''],
        submitButtonText: [''],
        sidebarTitle: [''],
        sidebarTips: this.fb.array([]),
    });

    constructor(
        public appearance: AppearanceEditor,
        private fb: FormBuilder,
        private settings: Settings,
    ) {}

    ngOnInit() {
        const data = this.settings.getJson(CONFIG_KEY, {}) as NewTicketPageContent;
        this.defaultValues = JSON.parse(this.appearance.defaultSettings[CONFIG_KEY] || null);

        this.form.patchValue(data);
        (data.sidebarTips || []).forEach(tip => this.addNewSidebarTip(tip));

        this.form.valueChanges.subscribe(value => {
            this.appearance.setConfig(CONFIG_KEY, value);
            this.appearance.addChanges({[CONFIG_KEY]: value});
        });
    }

    public sidebarTips(): FormArray {
        return this.form.get('sidebarTips') as FormArray;
    }

    public addNewSidebarTip(tip: {title?: string, content?: string} = {}) {
        this.sidebarTips().push(this.fb.group({title: [tip.title], content: [tip.content]}));
    }

    public removeSidebarTip(index: number) {
        this.sidebarTips().removeAt(index);
    }
}
