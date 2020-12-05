import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {ReportsService} from '../reports.service';
import {HelpCenterReportStateService} from './help-center-report-state.service';
import {finalize} from 'rxjs/operators';

@Component({
    selector: 'help-center-report',
    templateUrl: './help-center-report.component.html',
    styleUrls: ['./help-center-report.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [HelpCenterReportStateService],
})
export class HelpCenterReportComponent implements OnInit {
    constructor(
        private reports: ReportsService,
        public state: HelpCenterReportStateService,
    ) {}

    ngOnInit() {
        this.state.loading$.next(true);
        this.reports.getHelpCenterReport()
            .pipe(finalize(() => this.state.loading$.next(false)))
            .subscribe(response => {
                this.state.set(response.report);
            });
    }
}
