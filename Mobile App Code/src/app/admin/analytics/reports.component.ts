import {Component, ViewEncapsulation} from '@angular/core';
import {ReportsService} from './reports.service';
import {Settings} from '@common/core/config/settings.service';

@Component({
    selector: 'reports',
    templateUrl: './reports.component.html',
    styleUrls: ['./reports.component.scss'],
    encapsulation: ViewEncapsulation.None,
})

export class ReportsComponent {
    constructor(public reports: ReportsService, public settings: Settings) {}
}
