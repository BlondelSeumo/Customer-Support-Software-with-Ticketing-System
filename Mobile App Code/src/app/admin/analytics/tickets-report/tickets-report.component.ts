import {ChangeDetectionStrategy, Component, OnInit, ViewChild} from '@angular/core';
import {TicketsCountChartComponent} from './tickets-count-chart/tickets-count-chart';
import {TicketsByTagsChartComponent} from './tickets-by-tags-chart/tickets-by-tag-chart';
import {FirstResponseByHoursChartComponent} from './first-response-by-hours-chart/first-response-by-hours-chart';
import {TicketsByHourChartComponent} from './tickets-by-hour-chart/tickets-by-hour-chart.component';
import {ReportsService} from '../reports.service';
import {BehaviorSubject} from 'rxjs';
import {finalize} from 'rxjs/operators';
import {TicketReportForRange} from './ticket-report-for-range';
import {format, startOfDay, subDays, subMonths, subWeeks} from 'date-fns';

interface DateRange {
    period?: string;
    from?: string;
    to?: string;
}

type DataType = 'primary'|'compare';

@Component({
    selector: 'tickets-report',
    templateUrl: './tickets-report.component.html',
    styleUrls: ['./tickets-report.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush,
})

export class TicketsReportComponent implements OnInit {
    @ViewChild(TicketsCountChartComponent, { static: true }) ticketsCountChart: TicketsCountChartComponent;
    @ViewChild(TicketsByTagsChartComponent, { static: true }) ticketsByTagsChart: TicketsByTagsChartComponent;
    @ViewChild(FirstResponseByHoursChartComponent, { static: true }) firstResponseByHourChart: FirstResponseByHoursChartComponent;
    @ViewChild(TicketsByHourChartComponent, { static: true }) ticketsByHourChart: TicketsByHourChartComponent;

    public loading$ = new BehaviorSubject<boolean>(false);

    public dateRange = {
        primary: {} as DateRange,
        compare: {} as DateRange,
    };

    public reportData = {
        primary: {} as Partial<TicketReportForRange>,
        compare: {} as Partial<TicketReportForRange>,
    };

    constructor(private reports: ReportsService) {}

    ngOnInit() {
        this.setFiltersFromReadableString('last_30_days', 'primary');
        this.refreshReport('primary');
    }

    public refreshReport(dataType: DataType = 'primary') {
        this.loading$.next(true);
        this.reports.getTicketsReportForRange(this.getDateRangeObject(dataType))
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(response => {
                this.reportData[dataType] = response.data;

                this.ticketsCountChart.refresh(this.reportData.primary.dailyCounts, this.reportData.compare.dailyCounts);
                this.ticketsByTagsChart.refresh(this.reportData.primary.tags, this.reportData.compare.tags);
                this.firstResponseByHourChart.refresh(
                    this.reportData.primary.firstResponseTimes.breakdown,
                    this.reportData.compare.firstResponseTimes ? this.reportData.compare.firstResponseTimes.breakdown : null,
                );
                this.ticketsByHourChart.refresh(this.reportData.primary.hourlyCounts);
            });
    }

    public clearCompareData() {
        this.reportData.compare = {};
        this.refreshReport();
    }

    public setFiltersFromReadableString(timePeriod, dataType: DataType = 'primary', dateString?: string|number) {
        this.dateRange[dataType].period = timePeriod;
        if (timePeriod === 'custom') return;

        let from = dateString ? new Date(dateString) : new Date();
        const to = dateString ? new Date(dateString) : new Date();

        switch (timePeriod) {
            case 'last_7_days':
                from = subDays(from, 7);
                break;
            case 'today':
                from = startOfDay(from);
                break;
            case 'last_week':
                from = subWeeks(from, 1);
                break;
            case 'last_month':
                from = subMonths(from , 1);
                break;
            default:
                from = subDays(from, 30);
        }

        this.dateRange[dataType].from = format(from, 'yyyy-MM-dd');
        this.dateRange[dataType].to = format(to, 'yyyy-MM-dd');

        if (dataType === 'primary') {
            this.setFiltersFromReadableString(timePeriod, 'compare', from.toString());
        }
    }

    public setFiltersFromDateString(dateString: string, type: 'from'|'to' = 'from', dataType: DataType = 'primary') {
        this.dateRange[dataType][type] = format(new Date(dateString), 'yyyy-MM-dd');
    }

    private getDateRangeObject(dataType = 'primary') {
        const from = new Date(this.dateRange[dataType].from),
            to = new Date(this.dateRange[dataType].to);
        return {
            from_year: from.getFullYear(),
            from_month: from.getMonth() + 1,
            from_day: from.getDate(),
            to_year: to.getFullYear(),
            to_month: to.getMonth() + 1,
            to_day: to.getDate(),
        };
    }
}
