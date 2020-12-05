import {Component, Inject, ViewEncapsulation} from '@angular/core';
import {Article} from '../../../shared/models/Article';
import { MAT_DIALOG_DATA, MatDialogRef } from '@angular/material/dialog';

interface ArticleModalData {
    article: Article;
}

@Component({
    selector: 'article-modal',
    templateUrl: './article-modal.component.html',
    styleUrls: ['./article-modal.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class ArticleModalComponent {
    public article: Article;

    constructor(
        private dialogRef: MatDialogRef<ArticleModalComponent>,
        @Inject(MAT_DIALOG_DATA) public data: ArticleModalData,
    ) {
        this.hydrate();
    }

    public close() {
        this.dialogRef.close();
    }

    private hydrate() {
        this.article = this.data.article;
    }
}
