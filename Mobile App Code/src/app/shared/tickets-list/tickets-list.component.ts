import {Component, Input} from '@angular/core';
import {Router} from '@angular/router';
import {Ticket} from '../models/Ticket';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {ConversationModalComponent} from '../../conversation/conversation-modal/conversation-modal.component';
import {MailboxTagsService} from '../../ticketing/mailbox-tags.service';
import {DatatableService} from '../../../common/datatable/datatable.service';
import {Observable} from 'rxjs';

@Component({
    selector: 'tickets-list',
    templateUrl: './tickets-list.component.html',
    styleUrls: ['./tickets-list.component.scss'],
})
export class TicketsListComponent {
    @Input() openTicketInModal = false;

    public tickets$ = this.datatable.data$ as Observable<Ticket[]>;

    constructor(
        private router: Router,
        private modal: Modal,
        private mailboxTags: MailboxTagsService,
        public datatable: DatatableService<Ticket>,
    ) {}

    public openConversation(ticket: Ticket) {
        if (this.openTicketInModal) {
            this.modal.open(ConversationModalComponent, {ticketId: ticket.id}, {panelClass: 'conversation-modal-container'});
        } else {
            this.router.navigate(['mailbox/tickets', ticket.id], {queryParams: {tagId: this.mailboxTags.activeTagId$.value}});
        }
    }

    public ticketSelected(ticket: Ticket): boolean {
        return this.datatable.selectedRows$.value.includes(ticket.id);
    }

    public getTicketBody(ticket: Ticket): string {
        if (ticket.latest_reply && ticket.latest_reply.body) {
            return ticket.latest_reply.body;
        }

        if (ticket.replies && ticket.replies.length) {
            return ticket.replies[0].body;
        }
    }
}
