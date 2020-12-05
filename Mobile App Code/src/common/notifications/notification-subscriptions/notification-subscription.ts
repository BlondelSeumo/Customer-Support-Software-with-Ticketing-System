export interface NotificationSubscription {
    id?: number;
    name: string;
    notif_id: string;
    channels: {[key: string]: boolean};
}

export interface NotificationSubscriptionGroup {
    group_name: string;
    subscriptions: NotificationSubscription[];
}

export interface NotificationSubscriptionsResponse {
    all_notif_ids: string[];
    available_channels: string[];
    selections: {[key: string]: string[]};
    subscriptions: NotificationSubscriptionGroup[];
}
