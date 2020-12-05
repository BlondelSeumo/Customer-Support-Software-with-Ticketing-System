import {Component, ViewEncapsulation} from '@angular/core';
import {KeyValue} from '@angular/common';

@Component({
    selector: 'tickets-by-hour-chart',
    templateUrl: './tickets-by-hour-chart.component.html',
    styleUrls: ['./tickets-by-hour-chart.component.scss'],
    encapsulation: ViewEncapsulation.None,
})

export class TicketsByHourChartComponent {

    /**
     * Hourly ticket counts report data.
     */
    public report;

    /**
     * Refresh chart with given data.
     */
    public refresh(report) {
        this.report = report;
    }

    /**
     * Calculate opacity of chart box based on
     * number of tickets on that day, the more tickets
     * the darker bax background color should be
     */
    public getBoxColor(ticketCount) {
        let opacity = ticketCount / this.report.max;

        // round to at most 2 decimals
        opacity = Math.round(opacity * 100) / 100;

        // minimum opacity is 0.1
        if (opacity < 0.1) {
            opacity = 0.1;
        }

        // maximum opacity is 1
        if (opacity > 1) {
            opacity = 1;
        }

        return 'rgba(54, 162, 235, ' + opacity + ')';
    }

    public keyDescOrder(a: KeyValue<number, string>, b: KeyValue<number, string>) {
        const aKey = +a.key, bKey = +b.key;
        return aKey < bKey ? -1 : (bKey < aKey ? 1 : 0);
    }
}
