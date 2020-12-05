import {Component, Inject, ViewEncapsulation} from '@angular/core';
import {Users} from '@common/auth/users.service';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import {BackendErrorResponse} from '@common/core/types/backend-error-response';

@Component({
    selector: 'email-address-modal',
    templateUrl: './email-address-modal.component.html',
    styleUrls: ['./email-address-modal.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class EmailAddressModalComponent {
    public emailAddress: string;
    public errors = {};

    constructor(
        private dialogRef: MatDialogRef<EmailAddressModalComponent>,
        @Inject(MAT_DIALOG_DATA) public data: {userId: number},
        protected users: Users
    ) {}

    public close(emailAddress?: string) {
        this.dialogRef.close(emailAddress);
    }

    public confirm() {
        this.users.addEmail(this.data.userId, {emails: [this.emailAddress]}).subscribe(() => {
            this.close(this.emailAddress);
        }, (errorResponse: BackendErrorResponse) => this.errors = errorResponse.errors);
    }
}
