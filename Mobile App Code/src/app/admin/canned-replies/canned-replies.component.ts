import {Component, OnInit} from '@angular/core';
import {CannedRepliesService} from '../../ticketing/canned-replies/canned-replies.service';
import {CannedReply} from '../../shared/models/CannedReply';
import {CrupdateCannedReplyModalComponent} from '../../ticketing/canned-replies/crupdate-canned-reply-modal/crupdate-canned-reply-modal.component';
import {CurrentUser} from '@common/auth/current-user';
import {DatatableService} from '../../../common/datatable/datatable.service';
import {Observable} from 'rxjs';

@Component({
    selector: 'canned-replies',
    templateUrl: './canned-replies.component.html',
    styleUrls: ['./canned-replies.component.scss'],
    providers: [DatatableService],
})
export class CannedRepliesComponent implements OnInit {
    public cannedReplies$ = this.datatable.data$ as Observable<CannedReply[]>;
    constructor(
        public datatable: DatatableService<CannedReply>,
        private replies: CannedRepliesService,
        public currentUser: CurrentUser,
    ) {}

    ngOnInit() {
        this.datatable.init({
            uri: 'canned-replies',
            staticParams: {with: 'user'},
        });
    }

    public showCrupdateCannedReplyModal(cannedReply?: CannedReply) {
        this.datatable.openCrupdateResourceModal(
            CrupdateCannedReplyModalComponent,
            {cannedReply},
        ).subscribe();
    }

    public maybeDeleteCannedReplies() {
        this.datatable.confirmResourceDeletion('canned replies').subscribe(() => {
            this.replies.delete(this.datatable.selectedRows$.value).subscribe(() => {
                this.datatable.reset();
            });
        });
    }
}
