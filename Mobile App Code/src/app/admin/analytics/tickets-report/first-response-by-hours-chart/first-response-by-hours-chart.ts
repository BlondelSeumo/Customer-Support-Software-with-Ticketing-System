import {ChangeDetectionStrategy, Component} from '@angular/core';
import {ReplaySubject} from 'rxjs';
import {BarChartConfig, ChartType} from '@common/shared/charts/base-chart';
import {FirstResponseTimesBreakdown} from '../ticket-report-for-range';
import {Translations} from '@common/core/translations/translations.service';

@Component({
    selector: 'first-response-by-hours-chart',
    template: '<chart [chartConfig]="chartConfig | async" [height]="370"></chart>',
    changeDetection: ChangeDetectionStrategy.OnPush,
})

export class FirstResponseByHoursChartComponent {
    public chartConfig = new ReplaySubject<BarChartConfig>(1);

    constructor(private i18n: Translations) {}

    public refresh(primaryData: FirstResponseTimesBreakdown, compareData: FirstResponseTimesBreakdown) {
        this.createChart(primaryData, compareData);
    }

    private createChart(primaryData: FirstResponseTimesBreakdown, compareData: FirstResponseTimesBreakdown) {
        const dataset = [primaryData, compareData].map(data => {
            return Object.values(data || {}).map(d => d.percentage);
        });
        const labels = Object.keys(primaryData).map(hourRange => {
            return hourRange + ' ' + this.i18n.t('hours');
        });
        this.chartConfig.next({
            type: ChartType.BAR,
            labels: labels,
            data: dataset,
            tooltip: this.i18n.t('Percentage of all tickets:'),
            options: {
                axisY: {
                    labelInterpolationFnc: (val) => val + '%'
                }
            }
        });
    }
}
