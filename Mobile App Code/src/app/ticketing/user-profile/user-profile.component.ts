import {Component, OnInit, ViewChild, ViewEncapsulation} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {Ticket} from '../../shared/models/Ticket';
import {User} from '../../shared/models/User';
import {Email} from '../../shared/models/Email';
import {FormControl, FormGroup} from '@angular/forms';
import {Users} from '@common/auth/users.service';
import {Toast} from '@common/core/ui/toast.service';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {CurrentUser} from '@common/auth/current-user';
import {Settings} from '@common/core/config/settings.service';
import {debounceTime, distinctUntilChanged} from 'rxjs/operators';
import {EmailAddressModalComponent} from '../../shared/email-address-modal/email-address-modal.component';
import {openUploadWindow} from '@common/uploads/utils/open-upload-window';
import {UploadInputTypes} from '@common/uploads/upload-input-config';
import {AvatarValidator} from '@common/account-settings/avatar-validator';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';
import {MatTabChangeEvent} from '@angular/material/tabs';
import {BehaviorSubject} from 'rxjs';
import {SearchTermReport} from '../../admin/analytics/help-center-report/help-center-report';
import {ReportsService} from '../../admin/analytics/reports.service';
import {TagsManagerComponent} from '@common/tags/tags-manager/tags-manager.component';
import {DatatableService} from '../../../common/datatable/datatable.service';

@Component({
    selector: 'user-profile',
    templateUrl: './user-profile.component.html',
    styleUrls: ['./user-profile.component.scss'],
    providers: [DatatableService],
    encapsulation: ViewEncapsulation.None,
})
export class UserProfileComponent implements OnInit {
    @ViewChild(TagsManagerComponent, { static: true }) tagsManager: TagsManagerComponent;

    public user = new User({purchase_codes: []});
    public detailsEditable = false;
    public userSearches$ = new BehaviorSubject<SearchTermReport[]>(null);

    public profile = new FormGroup({
        details: new FormControl(),
        notes: new FormControl(),
    });

    public tagsControl = new FormControl([]);

    constructor(
        private users: Users,
        private route: ActivatedRoute,
        private toast: Toast,
        private modal: Modal,
        private avatarValidator: AvatarValidator,
        public currentUser: CurrentUser,
        public settings: Settings,
        private reports: ReportsService,
        public datatable: DatatableService<Ticket>,
    ) {}

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.hydrateProfile(data.user);
            this.bindFormControls();
            this.createDataSource();
        });

        this.detailsEditable = this.currentUser.hasPermission('users.update');
    }

    public openAddEmailModal() {
        this.modal.show(EmailAddressModalComponent, {userId: this.user.id}).afterClosed().subscribe(email => {
            this.user.secondary_emails.push(new Email({address: email}));
        });
    }

    public removeEmail(emailAddress: string) {
        this.users.removeEmail(this.user.id, {emails: [emailAddress]}).subscribe(() => {
            const index = this.user.secondary_emails.findIndex(email => email.address === emailAddress);
            this.user.secondary_emails.splice(index, 1);
        });
    }

    public openAvatarUploadDialog() {
        openUploadWindow({types: [UploadInputTypes.image]}).then(files => {
            if (this.avatarValidator.validateWithToast(files[0]).failed) return;

            this.users.uploadAvatar(this.user.id, files).subscribe(response => {
                this.user.avatar = response.user.avatar;
                this.currentUser.set('avatar', response.user.avatar);
                this.toast.open('Avatar updated');
            }, (errResponse: BackendErrorResponse) => {
                const key = Object.keys(errResponse.errors)[0];
                this.toast.open(errResponse.errors[key]);
            });
        });
    }

    public deleteAvatar() {
        this.users.deleteAvatar(this.user.id).subscribe(user => {
            this.user.avatar = user.avatar;
            this.toast.open('Avatar removed.');
        });
    }

    private hydrateProfile(user: User) {
        this.user = user;
        if (user.details) {
            this.profile.setValue({
                details: user.details.details,
                notes: user.details.notes,
            });
        }
        this.tagsControl.setValue(user.tags.map(tag => tag.name));
    }

    private createDataSource() {
        this.datatable.paginator.dontUpdateQueryParams = true;
        this.datatable.init({
            uri: 'tickets',
            staticParams: {userId: this.user.id},
        });
    }

    private bindFormControls() {
        this.profile.valueChanges
            .pipe(debounceTime(600), distinctUntilChanged())
            .subscribe(payload => {
                this.users.updateDetails(this.user.id, payload).subscribe(() => {
                    this.toast.open('Updated user details.');
                });
            });

        this.tagsControl.valueChanges.subscribe(tags => {
            this.users.syncTags(this.user.id, {tags}).subscribe();
        });
    }

    public tabChanged(e: MatTabChangeEvent) {
        if (e.index === 1 && !this.userSearches$.value) {
            this.reports.getUserSearchesReport(this.user.id)
                .subscribe(response => {
                    this.userSearches$.next(response.report);
                });
        }
    }
}
