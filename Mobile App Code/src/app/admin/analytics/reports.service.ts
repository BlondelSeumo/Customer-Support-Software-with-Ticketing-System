import {Injectable} from '@angular/core';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {BackendResponse} from '@common/core/types/backend-response';
import {EarningsData} from './envato-reports/earnings-chart/earning-chart-data';
import {EnvatoReportsFilters} from './envato-reports/envato-reports-filters';
import {TicketReportForRange} from './tickets-report/ticket-report-for-range';
import {
    HelpCenterReport,
    SearchTermReport
} from './help-center-report/help-center-report';

@Injectable({providedIn: 'root'})
export class ReportsService {
    constructor(private httpClient: AppHttpClient) {}

    public getEnvatoEarnings(filters: EnvatoReportsFilters = null): BackendResponse<{data: EarningsData}> {
        return this.httpClient.get('reports/envato/earnings', filters);
    }

    public getHelpCenterReport(): BackendResponse<{report: HelpCenterReport}> {
        return this.httpClient.get('reports/help-center');
    }

    public getUserSearchesReport(userId: number): BackendResponse<{report: SearchTermReport[]}> {
        return this.httpClient.get(`reports/user/${userId}/searches`);
    }

    public getTicketsReportForRange(params = null): BackendResponse<{data: TicketReportForRange}> {
        return this.httpClient.get('reports/tickets/range', params);
    }

    public getTicketCountsForMonth(month, year = null) {
        const payload: any = {from_month: month};
        if (year) payload.year = year;

        return this.httpClient.get('reports/tickets/count/daily', payload);
    }
}
