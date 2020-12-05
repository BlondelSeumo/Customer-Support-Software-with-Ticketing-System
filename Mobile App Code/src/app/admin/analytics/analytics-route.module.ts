import {NgModule} from '@angular/core';
import {RouterModule, Routes} from '@angular/router';
import {DefaultAnalyticsComponent} from '@common/admin/analytics/components/default-analytics/default-analytics.component';
import {AnalyticsHostComponent} from '@common/admin/analytics/components/analytics-host/analytics-host.component';
import {EnvatoReportsComponent} from './envato-reports/envato-reports.component';
import {TicketsReportComponent} from './tickets-report/tickets-report.component';
import {HelpCenterReportComponent} from './help-center-report/help-center-report.component';

const routes: Routes = [
    {
        path: '',
        component: AnalyticsHostComponent,
        children: [
            {path: '', redirectTo: 'conversations'},
            {path: 'google', component: DefaultAnalyticsComponent},
            {path: 'envato', component: EnvatoReportsComponent},
            {path: 'conversations', component: TicketsReportComponent},
            {path: 'search', component: HelpCenterReportComponent},
        ],
    }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class AnalyticsRoutingRoutingModule { }
