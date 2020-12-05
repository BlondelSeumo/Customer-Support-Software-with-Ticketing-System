import {
    Component,
    Input,
    Output,
    EventEmitter,
    ViewEncapsulation,
    ViewChild,
    OnInit
} from '@angular/core';
import {TicketsService} from '../tickets.service';
import {User} from '../../shared/models/User';
import {AGENT_PERMISSION, Ticket} from '../../shared/models/Ticket';
import {Users} from '@common/auth/users.service';
import {CurrentUser} from '@common/auth/current-user';
import {Toast} from '@common/core/ui/toast.service';
import { MatMenuTrigger } from '@angular/material/menu';
import {take} from 'rxjs/operators';

@Component({
    selector: 'assign-ticket-dropdown',
    templateUrl: './assign-ticket-dropdown.component.html',
    styleUrls: ['./assign-ticket-dropdown.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class AssignTicketDropdownComponent implements OnInit {
    @ViewChild(MatMenuTrigger, { static: true }) trigger: MatMenuTrigger;

    public agents: User[] = [];

    @Input() ticket: Ticket;
    @Input() ticketIds: number[];

    @Output() assigned = new EventEmitter();

    constructor(
        private users: Users,
        public currentUser: CurrentUser,
        private tickets: TicketsService,
        private toast: Toast,
    ) {}

    ngOnInit() {
        this.trigger.menuOpened
            .pipe(take(1))
            .subscribe(() => {
                this.fetchAgents();
            });
    }

    public open() {
        this.trigger.openMenu();
    }

    public close() {
        this.trigger.closeMenu();
    }

    public assignTicketsTo(userId: number = null) {
        const payload = this.ticketIds ? this.ticketIds : [this.ticket.id];

        this.tickets.assign(payload, userId).subscribe(() => {
            if (this.ticket) this.ticket.assigned_to = userId;
            this.toast.open('Tickets assigned');
            this.assigned.emit();
        });
    }

    public fetchAgents() {
        this.users.getAll({permission: AGENT_PERMISSION, perPage: 35}).subscribe((users: User[]) => {
            // filter out currently logged in user as there's already a 'me' dropdown item
            this.agents = users.filter(user => user.id !== this.currentUser.get('id'));
        });
    }
}
