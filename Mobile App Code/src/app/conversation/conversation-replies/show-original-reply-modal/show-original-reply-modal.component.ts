import {Component, ViewEncapsulation, ChangeDetectionStrategy, Inject} from '@angular/core';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';

interface ShowOriginalReplyModalData {
    original: {headers: any, body: {plain: string, html: string}};
}

@Component({
    selector: 'show-original-reply-modal',
    templateUrl: './show-original-reply-modal.component.html',
    styleUrls: ['./show-original-reply-modal.component.scss'],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ShowOriginalReplyModalComponent {
    public originalEmail: {headers: any, body: {plain: string, html: string}};
    public headerNames: string[] = [];

    constructor(
        private dialogRef: MatDialogRef<ShowOriginalReplyModalComponent>,
        @Inject(MAT_DIALOG_DATA) public data: ShowOriginalReplyModalData,
    ) {
        this.hydrate();
    }

    public close() {
        this.dialogRef.close();
    }

    private hydrate() {
        this.originalEmail = this.data.original;

        if (this.originalEmail && this.originalEmail.headers) {
            this.headerNames = Object.keys(this.originalEmail.headers);
        }
    }
}

