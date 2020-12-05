import {Component, OnDestroy, OnInit, ViewEncapsulation} from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {MailboxTagsService} from '../mailbox-tags.service';
import {Ticket} from '../../shared/models/Ticket';
import {EchoService} from '../../shared/broadcasting/echo.service';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {DatatableService} from '../../../common/datatable/datatable.service';
import {distinctUntilChanged} from 'rxjs/operators';
import {Subscription} from 'rxjs';

@Component({
    selector: 'agent-mailbox-ticket-list',
    templateUrl: './agent-mailbox-ticket-list.component.html',
    styleUrls: ['./agent-mailbox-ticket-list.component.scss'],
    encapsulation: ViewEncapsulation.None,
    providers: [DatatableService],
})
export class AgentMailboxTicketListComponent implements OnInit, OnDestroy {
    private mailboxSub: Subscription;
    constructor(
        private mailboxTags: MailboxTagsService,
        private router: Router,
        private modal: Modal,
        private route: ActivatedRoute,
        private backendEvents: EchoService,
        public datatable: DatatableService<Ticket>,
    ) {
        this.datatable.paginator.perPageCacheKey = 'agent-ticket-list-per-page';
    }

    ngOnInit() {
        this.datatable.init({
            uri: 'tickets',
            staticParams: {tagId: this.mailboxTags.activeTagId$.value},
        });
        this.mailboxSub = this.mailboxTags.activeTagId$.pipe(distinctUntilChanged()).subscribe(tagId => {
            this.datatable.reset({tagId});
        });
        this.bindToBackendEvents();
    }

    ngOnDestroy() {
        this.datatable.destroy();
        this.mailboxSub.unsubscribe();
    }

    private bindToBackendEvents() {
        this.backendEvents.ticketCreated.subscribe((newTicket: Ticket) => {
            // if new ticket does not have currently active status, bail
            if ( ! newTicket.tags || ! newTicket.tags.find(tag => tag.id === this.mailboxTags.activeTagId$.value)) return;

            // if ticket is already in tickets list, bail
            if (this.datatable.data.find(t => t.id === newTicket.id)) return;

            // add new ticket to tickets list and refresh mailbox tags
            newTicket.animated = true;
            this.datatable.data = [newTicket, ...this.datatable.data];
            this.mailboxTags.refresh();
        });
    }
}
