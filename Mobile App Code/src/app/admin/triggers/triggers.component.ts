import {Component, OnInit} from '@angular/core';
import {TriggersService} from './triggers.service';
import {Trigger} from '../../shared/models/Trigger';
import {CurrentUser} from '@common/auth/current-user';
import {DatatableService} from '../../../common/datatable/datatable.service';
import {Observable} from 'rxjs';

@Component({
    selector: 'triggers',
    templateUrl: './triggers.component.html',
    styleUrls: ['./triggers.component.scss'],
    providers: [DatatableService],
})

export class TriggerComponent implements OnInit {
    public triggers$ = this.datatable.data$ as Observable<Trigger[]>;
    constructor(
        public datatable: DatatableService<Trigger>,
        public currentUser: CurrentUser,
        private triggers: TriggersService
    ) {}

    ngOnInit() {
        this.datatable.init({
            uri: 'triggers',
        });
    }

    public maybeDeleteSelectedTriggers() {
        this.datatable.confirmResourceDeletion('triggers').subscribe(() => {
            this.triggers.delete(this.datatable.selectedRows$.value).subscribe(() => {
                this.datatable.reset();
            });
        });
    }
}
