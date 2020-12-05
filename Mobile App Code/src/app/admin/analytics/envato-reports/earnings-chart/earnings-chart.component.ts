import {ChangeDetectionStrategy, Component, OnInit} from '@angular/core';
import {ReportsService} from '../../reports.service';
import {EarningsChartData} from './earning-chart-data';
import {EnvatoReportsStateService} from '../envato-reports-state.service';
import {finalize} from 'rxjs/operators';
import {ChartType, LineChartConfig} from '@common/shared/charts/base-chart';
import {ReplaySubject} from 'rxjs';
import {Translations} from '@common/core/translations/translations.service';
import {SHORT_MONTH_NAMES} from '@common/core/utils/short-month-names';
import {EnvatoReportsFilters} from '../envato-reports-filters';

@Component({
    selector: 'earnings-chart',
    template: '<chart [chartConfig]="chartConfig | async" [height]="350"></chart>',
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class EarningsChartComponent implements OnInit {
    public chartConfig = new ReplaySubject<LineChartConfig>(1);
    public data: EarningsChartData;

    constructor(
        private reports: ReportsService,
        private state: EnvatoReportsStateService,
        private i18n: Translations,
    ) {}

    ngOnInit() {
        this.resetData();
        this.state.setActiveChart(this);
    }

    public refresh(filters: EnvatoReportsFilters, compareFilters: EnvatoReportsFilters) {
        this.state.loading$.next(true);
        if (compareFilters) {
            this.fetchDataForChart('secondary', compareFilters);
        } else {
            this.fetchDataForChart('primary', filters);
        }
    }

    private fetchDataForChart(type: 'primary'|'secondary' = 'primary', filters: EnvatoReportsFilters) {
        this.resetData('secondary');
        this.reports.getEnvatoEarnings({...filters, yearly: this.state.selectedPeriod === 'year'})
            .pipe(finalize(() => this.state.loading$.next(false)))
            .subscribe(response => {
                this.data[type] = response.data;
                this.createChart();
            });
    }

    private resetData(type: 'primary'|'secondary' = null) {
        if ( ! type) {
            this.data = {primary: {}, secondary: {}};
        } else if (type === 'secondary') {
            this.data.secondary = {};
        }
    }

    private createChart() {
        this.chartConfig.next({
            selector: '.earnings-chart',
            type: ChartType.LINE,
            labels: this.getChartLabels(),
            data: [this.getChartData('primary'), this.getChartData('secondary')],
        });
    }

    public getChartData(type: 'primary'|'secondary') {
        const periodKey = this.state.selectedPeriod === 'year' ? 'yearly' : 'monthly';
        return Object.values(this.data[type][periodKey] || {}).map(d => d.amount);
    }

    public getChartLabels() {
        return this.state.selectedPeriod === 'year' ?
            SHORT_MONTH_NAMES.map(m => this.i18n.t(m)) :
            Object.keys(this.data.primary.monthly);
    }
}
