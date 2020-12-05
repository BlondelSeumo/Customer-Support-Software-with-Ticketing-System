import {Component, Inject, ViewEncapsulation} from '@angular/core';
import {Reply} from '../../shared/models/Reply';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';
import {Settings} from '@common/core/config/settings.service';

interface ConfirmReplyDeleteModalComponentData  {
    reply: Reply;
}

@Component({
    selector: 'confirm-reply-delete-modal',
    templateUrl: './confirm-reply-delete-modal.component.html',
    encapsulation: ViewEncapsulation.None,
})
export class ConfirmReplyDeleteModalComponent {
    public type: string;

    constructor(
        private dialogRef: MatDialogRef<ConfirmReplyDeleteModalComponent>,
        @Inject(MAT_DIALOG_DATA) public data: ConfirmReplyDeleteModalComponentData,
        public settings: Settings,
    ) {
        this.type = this.getDisplayType(data.reply.type);
    }

    public close(confirmed = false) {
        this.dialogRef.close(confirmed);
    }

    public confirm() {
        this.close(true);
    }

    private getDisplayType(type: string): string {
        switch (type) {
            case 'replies':
                return 'reply';
            case 'notes':
                return 'note';
            case 'drafts':
                return 'draft';
        }
    }
}
