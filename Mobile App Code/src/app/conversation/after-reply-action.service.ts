import {Injectable} from '@angular/core';
import {TicketsService} from '../ticketing/tickets.service';
import {MailboxTagsService} from '../ticketing/mailbox-tags.service';
import {Router} from '@angular/router';
import {Toast} from '@common/core/ui/toast.service';
import {Settings} from '@common/core/config/settings.service';
import {LocalStorage} from '@common/core/services/local-storage.service';

@Injectable({
    providedIn: 'root',
})
export class AfterReplyAction {
    public defaultAction: string;
    private activeTicketId: number;

    constructor(
        private router: Router,
        private toast: Toast,
        private tickets: TicketsService,
        private settings: Settings,
        private mailboxTags: MailboxTagsService,
        private localStorage: LocalStorage,
    ) {
        this.defaultAction = this.settings.get('replies.after_reply_action', 'next_active_ticket');
    }

    public perform() {
        // we're currently in help center tickets list
        if (this.router.url.indexOf('help-center') > -1) {
            this.navigateToCustomerTicketsList();
            return;
        }

        switch (this.get()) {
            case 'next_active_ticket':
                this.navigateToNextActiveTicket();
                break;
            case 'back_to_folder':
                this.navigateToAgentTicketsList();
                break;
        }

        this.mailboxTags.refresh();
    }

    /**
     * Get currently active after redirect action.
     */
    public get() {
        return this.localStorage.get('after_reply_action', this.defaultAction);
    }

    /**
     * Change default action that is applied after reply submit.
     */
    public set(value: string = null) {
        this.defaultAction = value;
        this.localStorage.set('after_reply_action', value);
    }

    /**
     * Set currently active ticket id.
     */
    public setTicketId(id: number) {
        this.activeTicketId = id;
    }

    /**
     * Navigate to next active ticket belonging to currently active tag.
     */
    private navigateToNextActiveTicket() {
        const tagId = this.mailboxTags.activeTagId$.value;
        this.tickets.nextActiveTicket(tagId).subscribe(response => {
            if (response.ticket && response.ticket.id !== this.activeTicketId) {
                this.router.navigate(['/mailbox/tickets/', response.ticket.id], {queryParams: {tagId}});
            } else {
                this.navigateToAgentTicketsList();
            }
        });
    }

    /**
     * Navigate back to tickets list for currently active tag.
     */
    private navigateToAgentTicketsList() {
        this.router.navigate(['/mailbox/tickets'], {queryParams: {tagId: this.mailboxTags.activeTagId$.value}});
    }

    /**
     * Navigate back to customers ticket list.
     */
    public navigateToCustomerTicketsList(message?: string) {
        this.router.navigate(['/help-center/tickets']);
        this.toast.open(message || 'Your reply was submitted successfully.');
    }
}
