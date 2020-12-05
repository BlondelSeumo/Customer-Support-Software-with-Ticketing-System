import {Injectable} from '@angular/core';
import {BehaviorSubject} from 'rxjs';
import {EarningsChartComponent} from './earnings-chart/earnings-chart.component';
import {EarningsVsTicketsChartComponent} from './earnings-vs-tickets-chart/earnings-vs-tickets-chart.component';
import {EnvatoReportsFilters} from './envato-reports-filters';

type ChartComponent = EarningsChartComponent|EarningsVsTicketsChartComponent;
export type ChartPeriod = 'week' | 'month' | 'year' | 'vsTickets';

@Injectable({
    providedIn: 'root'
})
export class EnvatoReportsStateService {
    public loading$ = new BehaviorSubject<boolean>(false);
    public activeChart$ = new BehaviorSubject<ChartComponent>(null);
    public selectedPeriod: ChartPeriod = 'month';

    public get activeChart() {
        return this.activeChart$.value;
    }

    public filters: EnvatoReportsFilters = {};
    public compareFilters: EnvatoReportsFilters = {};

    public isLoading() {
        return this.loading$.value;
    }

    /**
     * Trigger change detection for filters object,
     * needed in order for chart directives to update properly.
     */
    public refreshActiveChart(withCompare = false) {
        if (this.isLoading() || ! this.activeChart) return;
        this.activeChart.refresh(this.filters, withCompare ? this.compareFilters : null);
    }

    public setActiveChart(chart: ChartComponent) {
        this.activeChart$.next(chart);
        this.refreshActiveChart();
    }
}
