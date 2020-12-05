import {
    Component,
    EventEmitter,
    HostBinding,
    Output,
    ViewEncapsulation
} from '@angular/core';
import {TicketsService} from '../tickets.service';
import {MailboxTagsService} from '../mailbox-tags.service';
import {Toast} from '@common/core/ui/toast.service';
import {CurrentUser} from '@common/auth/current-user';
import {Ticket} from '../../shared/models/Ticket';
import {Tag} from '@common/core/types/models/Tag';
import {DatatableService} from '../../../common/datatable/datatable.service';

@Component({
    selector: 'ticket-floating-toolbar',
    templateUrl: './ticket-floating-toolbar.component.html',
    styleUrls: ['./ticket-floating-toolbar.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class TicketFloatingToolbarComponent {
    @Output() onTicketsUpdated = new EventEmitter();

    @HostBinding('class.hidden') get noTicketsSelected() {
        return !this.datatable.selectedRows$.value.length;
    }

    constructor(
        private tickets: TicketsService,
        public mailboxTags: MailboxTagsService,
        private toast: Toast,
        public currentUser: CurrentUser,
        public datatable: DatatableService<Ticket>,
    ) {}

    public setStatusForSelectedTickets(tag: Tag) {
        this.tickets.changeMultipleTicketsStatus(this.datatable.selectedRows$.value, tag).subscribe(() => {
            this.ticketsUpdated();
        });
    }

    public maybeDeleteSelectedTickets() {
       this.datatable.confirmResourceDeletion('tickets').subscribe(() => {
           this.tickets.deleteMultiple(this.datatable.selectedRows$.value).subscribe(() => {
               this.ticketsUpdated();
               this.toast.open('Tickets deleted');
           });
       });
    }

    public ticketsUpdated() {
        this.onTicketsUpdated.emit();
        this.mailboxTags.refresh();
        this.datatable.reset();
    }
}
