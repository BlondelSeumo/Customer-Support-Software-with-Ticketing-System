import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ReplaySubject} from 'rxjs';
import {ChartType, LineChartConfig} from '@common/shared/charts/base-chart';
import {Translations} from '@common/core/translations/translations.service';
import {DailyCountsReport} from '../ticket-report-for-range';

@Component({
    selector: 'tickets-count-chart',
    template: '<chart [chartConfig]="chartConfig | async" [height]="350" class="rotate-labels"></chart>',
    changeDetection: ChangeDetectionStrategy.OnPush,
})

export class TicketsCountChartComponent {
    public chartConfig = new ReplaySubject<LineChartConfig>(1);

    constructor(private i18n: Translations) {}

    public refresh(data1: DailyCountsReport, data2: DailyCountsReport = null) {
        const formatted = this.prepareDatasets([data1, data2]);
        this.createChart(formatted.data, formatted.labels);
    }

    private prepareDatasets(datasets: [DailyCountsReport, DailyCountsReport]) {
        const labels = Object.values(datasets[0]).map(v => v.label);
        const data = [Object.values(datasets[0] || {}).map(v => v.count)];
        if (datasets[1]) {
            data[1] = Object.values(datasets[1]).map(v => v.count);
        }
        return {labels, data};
    }

    private createChart(datasets, labels) {
        this.chartConfig.next({
            type: ChartType.LINE,
            labels,
            tooltip: this.i18n.t('New Tickets:'),
            data: datasets,
        });
    }
}
