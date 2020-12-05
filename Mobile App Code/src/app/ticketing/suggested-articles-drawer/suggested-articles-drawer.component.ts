import {Component, Input, OnInit, EventEmitter, ViewEncapsulation, Output} from '@angular/core';
import {FormControl} from '@angular/forms';
import {HelpCenterService} from '../../help-center/shared/help-center.service';
import {HcUrls} from '../../help-center/shared/hc-urls.service';
import {Article} from '../../shared/models/Article';
import {catchError, debounceTime, distinctUntilChanged, switchMap} from 'rxjs/operators';
import {of} from 'rxjs';
import {SearchTermLoggerService} from '../../help-center/front/search-term-logger.service';

@Component({
    selector: 'suggested-articles-drawer',
    templateUrl: './suggested-articles-drawer.component.html',
    styleUrls: ['./suggested-articles-drawer.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class SuggestedArticlesDrawerComponent implements OnInit {
    @Input() public placeholder: string;
    @Input() public categories: number[];
    @Output() public inputValue = new EventEmitter();

    public searchQuery = new FormControl();
    public articles: Article[] = [];
    public searching = false;

    constructor(
        private helpCenter: HelpCenterService,
        public urls: HcUrls,
        private searchLogger: SearchTermLoggerService,
    ) {}

    public ngOnInit() {
        this.searchQuery.valueChanges
            .pipe(
                debounceTime(400),
                distinctUntilChanged(),
                switchMap(query => this.searchArticles(query)),
                catchError(() => of({pagination: {data: []}}))
            ).subscribe(response => {
                this.inputValue.emit(this.searchQuery.value);
                this.searching = false;
                this.setSearchResults(response.pagination.data);
            });
    }

    /**
     * Search help center articles by specified query.
     */
    private searchArticles(query: string) {
        if ( ! query) return of({pagination: {data: []}});
        this.searching = true;
        const params = {query, perPage: 4, categories: this.categories};
        return this.helpCenter.findArticles(params);
    }

    /**
     * Set specified search result on component instance.
     */
    private setSearchResults(articles: Article[]) {
        if ( ! articles || ! articles.length) {
            // if we've found no articles, wait until drawer
            // animation completes before removing old articles
            setTimeout(() => {
                this.articles = [];
            }, 300);
        } else {
            this.articles = articles;
        }
    }

    public updateSearchLogger() {
        this.searchLogger.updateSession({
            clickedArticle: true,
        });
    }
}
