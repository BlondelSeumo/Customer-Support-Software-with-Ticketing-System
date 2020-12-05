export interface EarningsData {
    items: EnvatoItemData[];
    monthly: MonthlyEarningChartData;
    sales: {
        date: string,
        day: number,
        order_id: number,
        item_id: number,
        amount: number,
        item: string,
        type: 'Sale',
    }[];
    totals: {
        sales: number,
        earnings: number,
    };
    yearly?: EnvatoYearlySales;
}

export interface MonthlyEarningChartData {
    [key: number]: {
        amount: number,
        sales: number
    };
}

export interface EarningsChartData {
    primary: Partial<EarningsData>;
    secondary: Partial<EarningsData>;
}

export interface EnvatoItemData {
    amount: number;
    sales: number;
    name: string;
    envato_id: number;
    percentage: number;
}

export interface EnvatoYearlySales {
    [key: number]: {
        sales: number;
        amount: number;
        month: number;
    };
}

