import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ReplaySubject} from 'rxjs';
import {ChartType, PieChartConfig} from '@common/shared/charts/base-chart';
import {TicketTagReport} from '../ticket-report-for-range';

@Component({
    selector: 'tickets-by-tags-chart',
    template: '<chart [chartConfig]="chartConfig | async" [height]="330"></chart>',
    changeDetection: ChangeDetectionStrategy.OnPush,
})

export class TicketsByTagsChartComponent {
    public chartConfig = new ReplaySubject<PieChartConfig>(1);

    public refresh(primaryData: TicketTagReport, compareData: TicketTagReport = {}) {
        this.createChart(primaryData, compareData);
    }

    private createChart(primaryData: TicketTagReport, compareData: TicketTagReport) {
        const dataset = [primaryData, compareData].map(data => {
            return Object.values(data || {}).map(d => d.count);
        });
        this.chartConfig.next({
            selector: '.tickets-by-tag-chart',
            type: ChartType.PIE,
            labels: Object.keys(primaryData || {}),
            tooltip: 'Number of tickets:',
            data: dataset[0],
            options: {
                showLabel: true,
                donut: true,
            }
        });
    }
}
