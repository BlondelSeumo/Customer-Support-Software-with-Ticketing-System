import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {ReportsService} from '../../reports.service';
import {forkJoin, ReplaySubject} from 'rxjs';
import {EnvatoReportsStateService} from '../envato-reports-state.service';
import {finalize} from 'rxjs/operators';
import {EarningsChartData, EarningsData} from '../earnings-chart/earning-chart-data';
import {ChartType, LineChartConfig} from '@common/shared/charts/base-chart';
import {EnvatoReportsFilters} from '../envato-reports-filters';
import {TicketReportForRange} from '../../tickets-report/ticket-report-for-range';

@Component({
    selector: 'earnings-vs-tickets-chart',
    template: '<chart [chartConfig]="chartConfig | async" [height]="350"></chart>',
    changeDetection: ChangeDetectionStrategy.OnPush,
})

export class EarningsVsTicketsChartComponent implements OnInit {
    public chartConfig = new ReplaySubject<LineChartConfig>(1);
    public data: EarningsChartData = {primary: {}, secondary: {}};

    constructor(
        private reports: ReportsService,
        private state: EnvatoReportsStateService,
    ) {}

    public refresh(filters: EnvatoReportsFilters) {
        this.state.loading$.next(true);
        forkJoin([
            this.reports.getEnvatoEarnings(filters),
            this.reports.getTicketsReportForRange({
                from_month: filters.month,
                from_day: filters.day,
                from_year: filters.year,
                to_month: filters.month,
                to_day: filters.to_day,
            })
        ])
        .pipe(finalize(() => this.state.loading$.next(false)))
        .subscribe(responses => {
            this.createChart(responses);
        });
    }

    ngOnInit() {
        this.state.setActiveChart(this);
    }

    private createChart(responses: [{data: EarningsData}, {data: TicketReportForRange}]) {
        // TODO: multiple ticket count by 20 temporarily as earnings are usually much higher when ticket count
        const ticketsData = Object.values(responses[1].data.dailyCounts || {}).map(v => v.count * 20),
            earningsData = Object.values(responses[0].data.monthly).map(d => d.amount);
        this.chartConfig.next({
            selector: '.earnings-vs-tickets-chart',
            type: ChartType.LINE,
            labels: Object.keys(responses[0].data.monthly),
            data: [earningsData, ticketsData],
        });
    }
}
