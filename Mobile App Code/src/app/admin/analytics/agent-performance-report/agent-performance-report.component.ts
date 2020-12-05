import {
    ChangeDetectionStrategy,
    Component,
    Input,
    OnChanges,
    OnDestroy,
    SimpleChanges
} from '@angular/core';
import {AgentPerformanceReport} from '../tickets-report/ticket-report-for-range';
import {DatatableService} from '../../../../common/datatable/datatable.service';

@Component({
    selector: 'agent-performance-report',
    templateUrl: './agent-performance-report.component.html',
    styleUrls: ['./agent-performance-report.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [DatatableService],
})
export class AgentPerformanceReportComponent implements OnChanges, OnDestroy {
    @Input() primaryData: AgentPerformanceReport[];
    @Input() compareData: AgentPerformanceReport[];

    constructor(
        public datatable: DatatableService<AgentPerformanceReport>,
    ) {
        this.datatable.init();
    }

    ngOnChanges(changes: SimpleChanges) {
        this.datatable.data = this.primaryData || [];
    }

    ngOnDestroy() {
        this.datatable.destroy();
    }
}
