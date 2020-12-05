export interface TicketReportForRange {
    newTickets: number;
    solvedTickets: number;
    openTickets: number;
    dailyCounts: DailyCountsReport;
    tags: TicketTagReport;
    replies: {
        replies: number,
        email: string,
        id: number,
        ticketsSolved: number,
        avgResponseTime: number
    }[];
    avgResponseTime: number;
    firstResponseTimes: {
        average: number,
        breakdown: FirstResponseTimesBreakdown,
    };
    hourlyCounts: {
        max: number,
        data: {day: string, counts: {[key: string]: number}}[]
    };
    agents: AgentPerformanceReport[];
}

export interface TicketTagReport {
    [key: string]: {
        count: number;
        name: string;
        percentage: string;
    };
}

export interface FirstResponseTimesBreakdown {
    [key: string]: {
        count: number;
        percentage: number;
    };
}

export interface AgentPerformanceReport {
    replies: number;
    ticketsSolved: number;
    avgResponseTime: number;
    id: number;
    email: string;
}

export interface DailyCountsReport {
    [key: string]: {label: string, count: number};
}
