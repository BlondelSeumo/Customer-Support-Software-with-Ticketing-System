import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {AppearanceEditor} from '@common/admin/appearance/appearance-editor/appearance-editor.service';
import {FormBuilder} from '@angular/forms';
import {Settings} from '@common/core/config/settings.service';
import {HelpCenterHeaderContent} from './help-center-header-content';

const CONFIG_KEY = 'hc.header.appearance';

@Component({
    selector: 'help-center-home-appearance-panel',
    templateUrl: './help-center-home-appearance-panel.component.html',
    styleUrls: ['./help-center-home-appearance-panel.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class HelpCenterHomeAppearancePanelComponent implements OnInit {
    public form = this.fb.group({
        title: [''],
        subtitle: [''],
        placeholder: [''],
        background: [''],
    });
    public defaultValues: HelpCenterHeaderContent;

    constructor(
        public appearance: AppearanceEditor,
        private fb: FormBuilder,
        private settings: Settings,
    ) {}

    ngOnInit() {
        const data = this.settings.getJson(CONFIG_KEY, {}) as HelpCenterHeaderContent;
        this.defaultValues = JSON.parse(this.appearance.defaultSettings[CONFIG_KEY] || null);

        this.form.patchValue(data);

        this.form.valueChanges.subscribe(value => {
            this.appearance.setConfig(CONFIG_KEY, value);
            this.appearance.addChanges({[CONFIG_KEY]: value});
        });
    }
}
