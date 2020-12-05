import {Component, ViewEncapsulation} from '@angular/core';
import {Settings} from '@common/core/config/settings.service';
import {HelpCenterHeaderContent} from '../../../admin/appearance/help-center-home-appearance-panel/help-center-header-content';

@Component({
    selector: 'hc-header',
    templateUrl: './hc-header.component.html',
    styleUrls: ['./hc-header.component.scss'],
    encapsulation: ViewEncapsulation.None,
})

export class HcHeaderComponent {
    public content: HelpCenterHeaderContent;
    constructor(public settings: Settings) {
        this.settings.all$().subscribe(() => {
            this.content = settings.getJson('hc.header.appearance', {});
        });
    }
}
