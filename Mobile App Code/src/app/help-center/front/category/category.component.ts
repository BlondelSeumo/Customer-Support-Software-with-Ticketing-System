import {Component, NgZone, OnInit, ViewEncapsulation} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {HcUrls} from '../../shared/hc-urls.service';
import {HelpCenterService} from '../../shared/help-center.service';
import {Category} from '../../../shared/models/Category';
import {Article} from '../../../shared/models/Article';
import {Settings} from '@common/core/config/settings.service';
import {ARTICLE_ORDER_OPTIONS} from '../../shared/article-order-options';
import {FormControl} from '@angular/forms';
import {InfiniteScroll} from '@common/core/ui/infinite-scroll/infinite.scroll';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';
import {BehaviorSubject} from 'rxjs';
import {finalize} from 'rxjs/operators';

@Component({
    selector: 'category',
    templateUrl: './category.component.html',
    styleUrls: ['./category.component.scss', './category-articles.scss'],
    encapsulation: ViewEncapsulation.None,
})

export class CategoryComponent extends InfiniteScroll implements OnInit {
    public category: Category;
    public articles: PaginationResponse<Article>;
    public orderControl = new FormControl(this.settings.get('articles.default_order'));
    public articleOrderOptions = ARTICLE_ORDER_OPTIONS;
    public loading$ = new BehaviorSubject<boolean>(null);

    constructor(
        private route: ActivatedRoute,
        private helpCenter: HelpCenterService,
        public urls: HcUrls,
        public settings: Settings,
        protected zone: NgZone,
    ) {
        super();
    }

    ngOnInit() {
        this.route.data.subscribe(data => {
            this.category = data.resolves.category;
            this.articles = data.resolves.articles;
        });
        this.orderControl.valueChanges.subscribe(value => {
            this.reloadArticles(value);
        });
        super.ngOnInit();
    }

    public reloadArticles(order?: string, page?: number) {
        this.loading$.next(true);
        const params = {
            categories: this.route.snapshot.params.categoryId,
            orderBy: order || this.orderControl.value,
            page: page || 1
        };
        this.helpCenter.getArticles(params)
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe(response => {
                if (page) {
                    this.articles = {...response.pagination, data: [...this.articles.data, ...response.pagination.data]};
                } else {
                    this.articles = response.pagination;
                }
            });
    }

    protected loadMoreItems() {
        this.reloadArticles(null, this.articles.current_page + 1);
    }

    protected canLoadMore(): boolean {
        return this.articles.last_page > this.articles.current_page;
    }

    protected isLoading(): boolean {
        return this.loading$.value;
    }
}
