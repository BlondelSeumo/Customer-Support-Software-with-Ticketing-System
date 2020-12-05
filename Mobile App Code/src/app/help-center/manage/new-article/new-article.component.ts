import {Component, OnInit, ViewChild, ViewEncapsulation} from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {ArticleSettingsModalComponent} from './article-settings-modal/article-settings-modal.component';
import {CategoriesManagerComponent} from '../categories-manager/categories-manager.component';
import {HelpCenterService} from '../../shared/help-center.service';
import {ArticleModalComponent} from '../../shared/article-modal/article-modal.component';
import {Article} from '../../../shared/models/Article';
import {CategoryModalComponent} from '../category-modal/category-modal.component';
import {TextEditorComponent} from '@common/text-editor/text-editor.component';
import {Toast} from '@common/core/ui/toast.service';
import {Modal} from '@common/core/ui/dialogs/modal.service';
import {UploadedFile} from '@common/uploads/uploaded-file';
import {FileEntry} from '@common/uploads/types/file-entry';
import {UploadQueueService} from '@common/uploads/upload-queue/upload-queue.service';
import {BackendErrorResponse} from '../../../../common/core/types/backend-error-response';
import {FormBuilder} from '@angular/forms';
import {slugifyString} from '../../../../common/core/utils/slugify-string';

@Component({
    selector: 'new-article',
    templateUrl: './new-article.component.html',
    styleUrls: ['./new-article.component.scss'],
    encapsulation: ViewEncapsulation.None,
    providers: [UploadQueueService],
})
export class NewArticleComponent implements OnInit {
    @ViewChild(TextEditorComponent, { static: true }) private textEditor: TextEditorComponent;
    @ViewChild(CategoriesManagerComponent, { static: true }) private categoriesManager: CategoriesManagerComponent;

    public updating = false;
    public attachments: FileEntry[] = [];

    public form = this.fb.group({
        title: [''],
        body: [''],
        slug: [''],
        description: [''],
        position: [0],
        draft: [1],
        categories: [[]],
        tags: [[]],
    });

    constructor(
        private modal: Modal,
        private helpCenter: HelpCenterService,
        private toast: Toast,
        public route: ActivatedRoute,
        private router: Router,
        private uploadQueue: UploadQueueService,
        private fb: FormBuilder,
    ) {}

    ngOnInit() {
        this.route.data.subscribe((data: {article?: Article}) => {
            if (data.article) {
                const article = data.article as Article;
                article.categories = article.categories.map(c => c.id) as any;
                article.tags = article.tags.map(t => t.name) as any;
                this.form.patchValue(article);
                this.textEditor.setContents(article.body);
                this.attachments = article.uploads || [];
            }
        });
    }

    public saveOrUpdateArticle() {
        this.updating = true;

        const request = this.route.snapshot.data.article ?
            this.helpCenter.updateArticle(this.route.snapshot.data.article.id, this.getPayload()) :
            this.helpCenter.createArticle(this.getPayload());

        request.subscribe(() => {
            this.toast.open('Article ' + (this.route.snapshot.data.article ? 'updated.' : 'created.'));
            this.router.navigateByUrl('help-center/manage/articles');
        }, (errResponse: BackendErrorResponse) => {
            const message = errResponse.errors[Object.keys(errResponse.errors)[0]];
            this.toast.open(message);
            this.updating = false;
        });
    }

    public openPreviewModal() {
        this.modal.open(
            ArticleModalComponent,
            {article: this.getPayload(false)},
            {panelClass: 'article-modal-container'}
        );
    }

    public openArticleSettingsModal() {
        this.modal.show(ArticleSettingsModalComponent, {form: this.form});
    }

    private getPayload(mapToIds = true) {
        const payload = this.form.value;
        payload.body = this.textEditor.getContents();
        if (mapToIds) {
            payload.uploads = this.attachments.map(a => a.id);
        }
        payload.slug = slugifyString(payload.slug);
        return payload;
    }

    public openNewCategoryModal() {
        this.modal.show(CategoryModalComponent)
            .afterClosed()
            .subscribe(category => {
                if ( ! category) return;
                this.categoriesManager.refresh().subscribe(() => {
                    this.categoriesManager.toggle(category);
                });
            });
    }

    public uploadFiles(files: UploadedFile[]) {
        this.uploadQueue.start(files).subscribe(response => {
            this.attachments = [...this.attachments, response.fileEntry];
        });
    }

    public removeAttachment(entry: FileEntry) {
        for (let i = 0; i < this.attachments.length; i++) {
            if (this.attachments[i].id === entry.id) {
                this.attachments.splice(i, 1);
            }
        }
    }
}
