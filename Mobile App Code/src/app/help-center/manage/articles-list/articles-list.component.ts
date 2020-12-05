import {Component, OnInit} from '@angular/core';
import {Router} from '@angular/router';
import {HelpCenterService} from '../../shared/help-center.service';
import {Article} from '../../../shared/models/Article';
import {DatatableService} from '../../../../common/datatable/datatable.service';
import {ARTICLE_ORDER_OPTIONS} from '../../shared/article-order-options';
import {FormControl, FormGroup} from '@angular/forms';
import {Settings} from '../../../../common/core/config/settings.service';
import {Tag} from '../../../../common/core/types/models/Tag';

@Component({
    selector: 'articles-list',
    templateUrl: './articles-list.component.html',
    styleUrls: ['./articles-list.component.scss'],
    providers: [DatatableService],
})
export class ArticlesListComponent implements OnInit {
    public selectedLayout = 'grid';
    public articleOrderOptions = ARTICLE_ORDER_OPTIONS;
    public form = new FormGroup({
        categories: new FormControl(''),
        tags: new FormControl([]),
        draft: new FormControl(null),
        order: new FormControl(this.settings.get('articles.default_order')),
    });

    constructor(
        private helpCenter: HelpCenterService,
        private router: Router,
        public datatable: DatatableService<Article>,
        private settings: Settings,
    ) {}

    ngOnInit() {
        this.datatable.init({
            uri: 'help-center/articles',
        });
        this.form.valueChanges.subscribe(value => {
            this.datatable.filters$.next(value);
        });
    }

    public goToUpdateArticle(articleId: number) {
        this.router.navigate(['/help-center/manage/', 'articles', articleId, 'edit']);
    }

    public maybeDeleteArticle(article: Article) {
        this.datatable.confirmResourceDeletion('article').subscribe(() => {
            this.helpCenter.deleteArticles([article.id]).subscribe(() => this.datatable.reset());
        });
    }

    public setLayout(name: string) {
        this.selectedLayout = name;
    }

    public isLayoutActive(name: string) {
        return this.selectedLayout === name;
    }

    public toggleTagFilter(tag: Tag) {
        const currentTags = this.form.get('tags').value as string[];
        const index = currentTags.indexOf(tag.name);
        if (index > -1) {
            currentTags.splice(index, 1);
        } else {
            currentTags.push(tag.name);
        }
        this.form.get('tags').setValue(currentTags);
    }
}
