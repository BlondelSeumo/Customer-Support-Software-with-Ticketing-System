import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {ChartsModule} from '@common/shared/charts/charts.module';
import {MatProgressBarModule} from '@angular/material/progress-bar';
import {MatTabsModule} from '@angular/material/tabs';
import {HelpCenterReportComponent} from './help-center-report/help-center-report.component';
import {ReportsComponent} from './reports.component';
import {EnvatoReportsComponent} from './envato-reports/envato-reports.component';
import {TicketsReportComponent} from './tickets-report/tickets-report.component';
import {EarningsChartComponent} from './envato-reports/earnings-chart/earnings-chart.component';
import {PercentageChangeComponent} from './percentage-change/percentage-change.component';
import {EarningsVsTicketsChartComponent} from './envato-reports/earnings-vs-tickets-chart/earnings-vs-tickets-chart.component';
import {TicketsCountChartComponent} from './tickets-report/tickets-count-chart/tickets-count-chart';
import {TicketsByTagsChartComponent} from './tickets-report/tickets-by-tags-chart/tickets-by-tag-chart';
import {FirstResponseByHoursChartComponent} from './tickets-report/first-response-by-hours-chart/first-response-by-hours-chart';
import {TicketsByHourChartComponent} from './tickets-report/tickets-by-hour-chart/tickets-by-hour-chart.component';
import {ArticleReportTableComponent} from './help-center-report/article-report-table/article-report-table.component';
import {AnalyticsHostComponent} from '@common/admin/analytics/components/analytics-host/analytics-host.component';
import {DefaultAnalyticsComponent} from '@common/admin/analytics/components/default-analytics/default-analytics.component';
import {AnalyticsHeaderComponent} from '@common/admin/analytics/components/analytics-header/analytics-header.component';
import {AnalyticsRoutingRoutingModule} from './analytics-route.module';
import {MatIconModule} from '@angular/material/icon';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {FormatPipesModule} from '@common/core/ui/format-pipes/format-pipes.module';
import {InfoPopoverModule} from '@common/core/ui/info-popover/info-popover.module';
import {DatepickerComponent} from '../../shared/datepicker/datepicker.component';
import {FormsModule} from '@angular/forms';
import {MatButtonModule} from '@angular/material/button';
import {MatTooltipModule} from '@angular/material/tooltip';
import {SearchReportTableModule} from './help-center-report/search-report-table/search-report-table.module';
import {DatatableModule} from '../../../common/datatable/datatable.module';
import {AgentPerformanceReportComponent} from './agent-performance-report/agent-performance-report.component';
import {EnvatoItemSalesTableComponent} from './envato-reports/envato-item-sales-table/envato-item-sales-table.component';
import {EnvatoMonthlySalesTableComponent} from './envato-reports/envato-mothly-sales-table/envato-monthly-sales-table.component';

@NgModule({
    imports: [
        AnalyticsRoutingRoutingModule,
        CommonModule,
        ChartsModule,
        TranslationsModule,
        FormatPipesModule,
        InfoPopoverModule,
        FormsModule,
        SearchReportTableModule,
        DatatableModule,

        // material
        MatProgressBarModule,
        MatTabsModule,
        MatIconModule,
        MatButtonModule,
        MatTooltipModule,
    ],
    declarations: [
        AnalyticsHostComponent,
        AnalyticsHeaderComponent,
        DefaultAnalyticsComponent,
        DatepickerComponent,

        // APP
        HelpCenterReportComponent,
        ReportsComponent,
        EnvatoReportsComponent,
        EnvatoItemSalesTableComponent,
        EnvatoMonthlySalesTableComponent,
        TicketsReportComponent,
        AgentPerformanceReportComponent,
        EarningsChartComponent,
        PercentageChangeComponent,
        EarningsVsTicketsChartComponent,
        TicketsCountChartComponent,
        TicketsByTagsChartComponent,
        FirstResponseByHoursChartComponent,
        TicketsByHourChartComponent,
        ArticleReportTableComponent,
    ]
})
export class AnalyticsModule {
}
