import {Component, OnDestroy, OnInit} from '@angular/core';
import {CurrentUser} from '@common/auth/current-user';
import {Ticket} from '../../shared/models/Ticket';
import {Tag} from '../../shared/models/Tag';
import {ActivatedRoute, Router} from '@angular/router';
import {FormControl} from '@angular/forms';
import {DatatableService} from '../../../common/datatable/datatable.service';
import {Observable} from 'rxjs';

@Component({
    selector: 'customer-tickets-list',
    templateUrl: './customer-tickets-list.component.html',
    styleUrls: ['./customer-tickets-list.component.scss'],
    providers: [DatatableService],
})
export class CustomerTicketsListComponent implements OnInit, OnDestroy {
    public tickets$ = this.datatable.data$ as Observable<Ticket[]>;
    public statusFormControl = new FormControl(this.route.snapshot.queryParams.status || '');

    constructor(
        public currentUser: CurrentUser,
        private router: Router,
        private route: ActivatedRoute,
        public datatable: DatatableService<Ticket>,
    ) {}

    ngOnInit() {
        this.datatable.init({
            uri: 'tickets',
            staticParams: {userId: this.currentUser.get('id')},
        });

        this.statusFormControl.valueChanges.subscribe(value => {
            this.datatable.addFilter('tagId', value);
        });
    }

    ngOnDestroy() {
        this.datatable.destroy();
    }

    public getStatus(ticket: Ticket): Tag {
        return ticket.tags.filter(tag => tag.type === 'status')[0];
    }

    public openConversation(ticket: Ticket) {
        this.router.navigate(['/help-center/tickets', ticket.id]);
    }
}
