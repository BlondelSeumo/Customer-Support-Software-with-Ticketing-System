import {ApplicationRef, EventEmitter, Injectable, NgZone} from '@angular/core';
import {Ticket} from '../models/Ticket';
import {Reply} from '../models/Reply';
import {Settings} from '@common/core/config/settings.service';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {CurrentUser} from '@common/auth/current-user';
import {Channel} from 'laravel-echo/dist/channel';
import {TicketReplyCreatedPayload} from './types/ticket-reply-created-payload';
import {TicketsService} from '../../ticketing/tickets.service';
import {
    BroadcastNotification,
    DatabaseNotification
} from '@common/notifications/database-notification';
import {NotificationService} from '@common/notifications/notification-list/notification.service';
import {stripTags} from '@common/core/utils/strip-tags';
import {filter, take} from 'rxjs/operators';
import {not} from 'rxjs/internal-compatibility';

@Injectable({
    providedIn: 'root'
})
export class EchoService {
    public connected = false;
    public ticketCreated: EventEmitter<Ticket> = new EventEmitter();
    public ticketReplyCreated: EventEmitter<Reply> = new EventEmitter();

    public echo;
    private ticketsChannel: Channel;

    constructor(
        private settings: Settings,
        private httpClient: AppHttpClient,
        private currentUser: CurrentUser,
        private tickets: TicketsService,
        private notifications: NotificationService,
        private appRef: ApplicationRef,
        private zone: NgZone,
    ) {}

    public init() {
        if (this.connected || ! this.shouldInitPusher()) return;

        this.appRef.isStable
            .pipe(filter(isStable => isStable), take(1))
            .subscribe(async () => {
                const { Echo } = await import('./barrel');

                this.echo = new Echo({
                    broadcaster: 'pusher',
                    key: this.settings.get('realtime.pusher_key'),
                    authEndpoint: 'secure/broadcasting/auth',
                    cluster: this.settings.get('realtime.pusher_cluster'),
                });

                // tickets events
                this.ticketsChannel = this.echo.channel('tickets')
                    .listen('TicketReplyCreated', e => this.onTicketReplyCreated(e))
                    .listen('TicketCreated', e => this.onTicketCreated(e));

                // laravel notification broadcasts
                this.echo.private('App.User.' + this.currentUser.get('id'))
                    .notification((data: BroadcastNotification) => {
                        this.notifications.add(this.transformBroadcastNotif(data));
                        const notif = new Notification(
                            stripTags(data.lines[0].content),
                            {
                                icon: data.image,
                                badge: data.image,
                                tag: data.type,
                                renotify: true,
                            });
                        notif.onclick = () => {
                            window.open(data.mainAction.action, '_blank');
                        };
                    });

                this.connected = true;
            });
    }

    private onTicketReplyCreated(payload: TicketReplyCreatedPayload) {
        this.tickets.getReply(payload.replyId, null, {suppressAuthToast: true}).subscribe(response => {
            this.zone.run(() => {
                this.ticketReplyCreated.emit(response.reply);
            });
        });
    }

    private onTicketCreated(payload: {ticket: Ticket}) {
        this.tickets.get(payload.ticket.id, null, {suppressAuthToast: true}).subscribe(response => {
            this.zone.run(() => {
                this.ticketCreated.emit(response.ticket);
            });
        });
    }

    private shouldInitPusher() {
        return this.currentUser.isLoggedIn() && this.settings.get('realtime.pusher_key') && this.settings.get('realtime.enable');
    }

    private transformBroadcastNotif(notif: BroadcastNotification): DatabaseNotification {
        return {
            id: notif.id,
            type: notif.type,
            data: notif,
            time_period: 'today',
            read_at: null,
            relative_created_at: '1 minute ago',
        };
    }
}
