import {ChangeDetectionStrategy, ChangeDetectorRef, Component, OnInit} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {
    NotificationSubscriptionGroup,
    NotificationSubscriptionsResponse
} from '@common/notifications/notification-subscriptions/notification-subscription';
import {
    NotificationSubscriptionsService,
    UpdateNotificationSubscriptionsPayload
} from '@common/notifications/notification-subscriptions/notification-subscriptions.service';
import {CurrentUser} from '@common/auth/current-user';
import {Toast} from '@common/core/ui/toast.service';
import {BehaviorSubject} from 'rxjs';
import {delay, finalize} from 'rxjs/operators';
import {SelectionModel} from '@angular/cdk/collections';
import {Settings} from '@common/core/config/settings.service';

@Component({
    selector: 'notification-subscriptions',
    templateUrl: './notification-subscriptions.component.html',
    styleUrls: ['./notification-subscriptions.component.scss'],
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class NotificationSubscriptionsComponent implements OnInit {
    public subscriptions: NotificationSubscriptionGroup[];
    public loading$ = new BehaviorSubject<boolean>(false);
    public supportsBrowserNotifications = ('Notification' in window);
    public availableChannels: string[] = [];
    public selections: {[key: string]: SelectionModel<string>} = {};
    public allNotifIds: string[] = [];

    constructor(
        private route: ActivatedRoute,
        private api: NotificationSubscriptionsService,
        private currentUser: CurrentUser,
        private toast: Toast,
        private cd: ChangeDetectorRef,
        public settings: Settings,
    ) {}

    ngOnInit() {
        this.route.data.subscribe((data: {api: NotificationSubscriptionsResponse}) => {
            this.subscriptions = data.api.subscriptions;
            this.availableChannels = data.api.available_channels;
            this.allNotifIds = data.api.all_notif_ids;
            this.availableChannels.forEach(channelName => {
                this.selections[channelName] = new SelectionModel(true, data.api.selections[channelName]);
            });
        });

        if (Notification.permission !== 'granted') {
            this.bindToBrowserNotifSubscription();
        }
    }

    public toggleAllRowsFor(channelName: string) {
        this.allRowsSelectedFor(channelName) ?
            this.selections[channelName].clear() :
            this.selections[channelName].select(...this.allNotifIds);
    }

    public allRowsSelectedFor(channelName: string): boolean {
        return this.selections[channelName].selected.length === this.allNotifIds.length;
    }

    public saveSettings() {
        this.loading$.next(true);
        const payload = this.getPayload();
        this.api.updateUserSubscriptions(this.currentUser.get('id'), payload)
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(() => {
                this.toast.open('Notification settings updated.');
            });
    }

    private getPayload(): UpdateNotificationSubscriptionsPayload {
        const payload = {};
        Object.keys(this.selections).forEach(channelName => {
            payload[channelName] = this.selections[channelName].selected;
        });
        return payload;
    }

    private bindToBrowserNotifSubscription() {
        this.selections.browser.changed
            .pipe(delay(1))
            .subscribe(e => {
                if (e.added.length && !e.removed.length) {
                    if (Notification.permission === 'denied') {
                        this.toast.open('Notifications blocked. Please enable them for this site from browser settings.');
                        this.selections.browser.clear();
                        this.cd.markForCheck();
                    } else {
                        Notification.requestPermission().then(permission => {
                            if (permission !== 'granted') {
                                this.selections.browser.clear();
                                this.cd.markForCheck();
                            }
                        });
                    }
                }
            });
    }
}
