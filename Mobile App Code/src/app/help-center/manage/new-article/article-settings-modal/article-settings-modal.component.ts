import {Component, Inject, ViewEncapsulation} from '@angular/core';
import {MAT_DIALOG_DATA, MatDialogRef} from '@angular/material/dialog';
import {FormGroup} from '@angular/forms';

interface ArticleSettingsModalData {
    form: FormGroup;
}

interface ArticleSettingsModel {
    slug?: string;
    description?: string;
    position?: number|string;
}

@Component({
    selector: 'article-settings-modal',
    templateUrl: './article-settings-modal.component.html',
    encapsulation: ViewEncapsulation.None,
})
export class ArticleSettingsModalComponent {
    constructor(
        private dialogRef: MatDialogRef<ArticleSettingsModalComponent>,
        @Inject(MAT_DIALOG_DATA) public data: ArticleSettingsModalData,
    ) {}

    public model: ArticleSettingsModel = {};

    public close() {
        this.dialogRef.close();
    }
}
