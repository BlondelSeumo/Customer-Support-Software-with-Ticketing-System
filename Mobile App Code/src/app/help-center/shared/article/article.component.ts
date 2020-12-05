import {
    AfterViewChecked,
    Component,
    Input,
    OnInit,
    ViewEncapsulation
} from '@angular/core';
import {ActivatedRoute, Router} from '@angular/router';
import {DomSanitizer, SafeHtml} from '@angular/platform-browser';
import {HelpCenterService, ShowArticleResponse} from '../help-center.service';
import {Article} from '../../../shared/models/Article';
import * as Prism from 'prismjs';
import 'prismjs/components/prism-markup';
import 'prismjs/components/prism-markup-templating';
import 'prismjs/components/prism-php';
import 'prismjs/components/prism-typescript';
import 'prismjs/components/prism-javascript';
import {CurrentUser} from '@common/auth/current-user';
import {Settings} from '@common/core/config/settings.service';
import {FileEntry} from '@common/uploads/types/file-entry';
import {prettyBytes} from '@common/core/utils/pretty-bytes';

interface ArticleAttachment extends FileEntry {
    downloadUrl: string;
    prettySize: string;
}

@Component({
    selector: 'article',
    templateUrl: './article.component.html',
    styleUrls: ['./article.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class ArticleComponent implements OnInit, AfterViewChecked {

    /**
     * Full article model, if this is passed in
     * there's no need to fetch article by url params.
     */
    @Input() public article: Article;

    /**
     * Trusted article body (includes html) for the view.
     */
    public trustedArticleBody: SafeHtml;

    /**
     * Whether code has already been highlighted.
     * (for article content, and not component).
     */
    public codeHighlighted = false;
    public attachments: ArticleAttachment[] = [];
    public downloadAllAttachmentsUrl: string;

    constructor(
        private sanitizer: DomSanitizer,
        private route: ActivatedRoute,
        private router: Router,
        private helpCenter: HelpCenterService,
        public user: CurrentUser,
        public settings: Settings,
    ) {}

    ngOnInit() {
        if (this.article) {
            return this.initArticle(this.article);
        }
        this.route.data.subscribe((d: {articleResponse: ShowArticleResponse}) => {
            this.codeHighlighted = false;

            if (d?.articleResponse?.article) {
                this.initArticle(d?.articleResponse?.article);
            } else {
                this.getArticle(this.route.snapshot.params);
            }
        });
    }

    ngAfterViewChecked() {
        if ( ! this.codeHighlighted && Prism) {
            Prism.highlightAll();
            this.codeHighlighted = true;
        }
    }

    private initArticle(article: Article) {
        this.article = article;
        this.trustedArticleBody = this.sanitizer.bypassSecurityTrustHtml(article.body);
        const baseDownloadUrl = `${this.settings.getBaseUrl(true)}secure/help-center/articles/${article.id}/download`;
        this.attachments = (article.uploads || []).map((upload: ArticleAttachment) => {
            upload.prettySize = prettyBytes(upload.file_size);
            upload.downloadUrl = `${baseDownloadUrl}/${upload.hash}`;
            return upload;
        });
        if (this.attachments.length > 1) {
            const hashes = this.attachments.map(a => a.hash).join(',');
            this.downloadAllAttachmentsUrl = `${baseDownloadUrl}/${hashes}`;
        }
    }

    private getArticle(urlParams) {
        this.helpCenter.getArticle(urlParams['article']).subscribe(response => {
            this.initArticle(response.article);
        }, () => {
            this.router.navigate(['/help-center']);
        });
    }
}
