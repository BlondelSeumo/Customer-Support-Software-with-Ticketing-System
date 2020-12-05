import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit} from '@angular/core';
import {KeyValue} from '@angular/common';
import {MonthlyEarningChartData} from './earnings-chart/earning-chart-data';
import {ChartPeriod, EnvatoReportsStateService} from './envato-reports-state.service';
import {delay, distinctUntilChanged, take} from 'rxjs/operators';
import {endOfISOWeek, endOfMonth, startOfISOWeek, startOfMonth, subMonths} from 'date-fns';
import {Translations} from '@common/core/translations/translations.service';
import {SHORT_MONTH_NAMES} from '@common/core/utils/short-month-names';

@Component({
    selector: 'envato-reports',
    templateUrl: './envato-reports.component.html',
    styleUrls: ['./envato-reports.component.scss'],
    providers: [EnvatoReportsStateService],
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class EnvatoReportsComponent implements OnInit {
    public activeTab = 'earnings';

    constructor(
        public state: EnvatoReportsStateService,
        private cd: ChangeDetectorRef,
        private i18n: Translations,
    ) {}

    ngOnInit() {
        this.resetFilters();
    }

    public showEarningsFor(period: ChartPeriod = 'week', keepChart = false) {
        if (this.state.isLoading()) return;
        if (!keepChart) {
            this.state.activeChart$.next(null);
        }
        this.state.selectedPeriod = period;

        const now = new Date();
        this.state.filters = {
            year: now.getFullYear(),
            month: now.getMonth() + 1,
            day: (period === 'week' ? startOfISOWeek(now) : startOfMonth(now)).getDate(),
            to_day: (period === 'week' ? endOfISOWeek(now) : endOfMonth(now)).getDate(),
        };

        this.state.activeChart$
            .pipe(distinctUntilChanged(), delay(100), take(1))
            .subscribe(() => {
                this.cd.markForCheck();
                this.state.refreshActiveChart();
            });
    }

    public updateFiltersFromDate(dateString: string, type = null) {
        const date = new Date(dateString);
        const filters = {
            year: date.getFullYear(),
            month: date.getMonth() + 1,
            day: date.getDate(),
            to_day: endOfMonth(date).getDate(),
        };
        if (type === 'compare') {
            this.state.compareFilters = filters;
        } else {
            this.state.filters = filters;
        }
    }

    private resetFilters() {
        const now = new Date();
        const currentYear = now.getFullYear();
        const currentMonth = now.getMonth() + 1;
        const lastOfMonth = endOfMonth(now).getDate();
        const compareLastOfMonth =  endOfMonth(subMonths(now, 1)).getDate();
        this.state.filters = {year: currentYear, month: currentMonth, day: 1, to_day: lastOfMonth};
        this.state.compareFilters = {year: currentYear, month: currentMonth - 1, day: 1, to_day: compareLastOfMonth};
    }

    /**
     * Get array of days in a given month.
     */
    public getDaysInMonth(year, month, startFrom = null): number[] {
        const daysInMonth = new Date(year, month, 0).getDate();

        const days = [];
        for (let i = 1; i <= daysInMonth; i++) {
            days.push(i);
        }

        if (startFrom) {
            days.splice(0, startFrom);
        }

        return days;
    }

    public getYearList(): number[] {
        const currentYear = new Date().getFullYear(), years = [];
        let startYear = 2013;
        while (startYear <= currentYear) {
            years.push(startYear++);
        }
        return years;
    }

    public sliceMonthlyDataUntilToday(data: MonthlyEarningChartData) {
        const currentDay = new Date().getDate(), newData = {};
        Object.keys(data)
            .filter(day => +day <= currentDay)
            .forEach(day => newData[day] = data[day]);
        return newData;
    }

    public keyDescOrder(a: KeyValue<number, { sales: number, amount: number }>, b: KeyValue<number, { sales: number, amount: number }>) {
        const aKey = +a.key, bKey = +b.key;
        return aKey < bKey ? -1 : (bKey < aKey ? 1 : 0);
    }

    public monthNames() {
        return SHORT_MONTH_NAMES.map(m => this.i18n.t(m));
    }
}
