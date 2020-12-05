import {Component, OnInit, Input, ViewEncapsulation, ViewChild, ElementRef, Output, EventEmitter} from '@angular/core';
import {FormControl} from '@angular/forms';
import {HelpCenterService} from '../shared/help-center.service';
import {HcUrls} from '../shared/hc-urls.service';
import {Article} from '../../shared/models/Article';
import {Params, Router} from '@angular/router';
import {DomSanitizer, SafeHtml} from '@angular/platform-browser';
import {of} from 'rxjs';
import {catchError, debounceTime, distinctUntilChanged, switchMap} from 'rxjs/operators';
import { MatAutocompleteSelectedEvent } from '@angular/material/autocomplete';
import {Category} from '../../shared/models/Category';
import {EMPTY_PAGINATION_RESPONSE} from '@common/core/types/pagination/pagination-response';
import {SearchTermLoggerService} from '../front/search-term-logger.service';

@Component({
    selector: 'suggested-articles-dropdown',
    templateUrl: './suggested-articles-dropdown.component.html',
    styleUrls: ['./suggested-articles-dropdown.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class SuggestedArticlesDropdownComponent implements OnInit {
    @ViewChild('input', { static: true }) searchInput: ElementRef<HTMLInputElement>;

    @Input() category: Category;
    @Input() placeholder: string;
    @Input() openInNewPage = false;
    @Input() forHomepage = false;
    @Output() articleSelected = new EventEmitter<Article>();

    public searchQuery = new FormControl();
    public articles: Article[] = [];
    public searching = false;
    public haveSearched = false;
    public autocompleteWidth: number;

    // need to store it manually as angular material
    // resets observable value to null on click of result
    private lastSearchQuery: string;

    constructor(
        private helpCenter: HelpCenterService,
        public urls: HcUrls,
        private router: Router,
        private sanitizer: DomSanitizer,
        private searchLogger: SearchTermLoggerService,
    ) {}

    public ngOnInit() {
        this.bindToSearchQueryControl();
    }

    public focus() {
        this.searchInput && this.searchInput.nativeElement.focus();
    }

    public viewAllResults() {
        if ( ! this.searchQuery.value) return;
        const queryParams: Params = {};
        if (this.category) {
            queryParams.categoryId = this.category.id;
        }
        this.router.navigate(this.urls.searchPage(this.searchQuery.value), {queryParams});
    }

    public trustArticleBody(body: string): SafeHtml {
        return this.sanitizer.bypassSecurityTrustHtml(body);
    }

    public getWidth(): number {
        // need to add 40px padding around search dropdown on homepage
        if (this.forHomepage) {
            if (this.autocompleteWidth) return this.autocompleteWidth;
            this.autocompleteWidth = this.searchInput.nativeElement.offsetWidth + 40;
            return this.autocompleteWidth;
        }
    }

    private bindToSearchQueryControl() {
        this.searchQuery.valueChanges
            .pipe(
                debounceTime(250),
                distinctUntilChanged(),
                switchMap(query => this.searchArticles(query)),
                catchError(() => of(EMPTY_PAGINATION_RESPONSE)),
            ).subscribe(response => {
                this.setArticles(response.pagination.data);
                this.haveSearched = true;
                this.searching = false;
            });
    }

    private searchArticles(query: string) {
        this.lastSearchQuery = query;
        if ( ! query) return of(EMPTY_PAGINATION_RESPONSE);
        this.searching = true;
        const params = {query, categories: this.category ? [this.category.id] : null};
        return this.helpCenter.findArticles(params);
    }


    private setArticles(articles: Article[]) {
        if ( ! articles) articles = [];
        this.articles = articles;
    }

    public onArticleSelected(e: MatAutocompleteSelectedEvent) {
        const article = e.option.value as Article;

        // if article is not available, user clicked on "view all results" button
        if ( ! article && this.lastSearchQuery) {
            this.router.navigate(this.urls.searchPage(this.lastSearchQuery));
        }

        // user clicked on specific article
        if (article) {
            if (this.articleSelected.observers.length) {
                this.articleSelected.emit(article);
            } else {
                this.searchLogger.updateSessionAndStore({clickedArticle: true});
                this.router.navigate(this.urls.article(article));
            }
        }

        this.resetSearchQuery();
    }

    public resetSearchQuery() {
        this.searchQuery.reset();
        this.lastSearchQuery = null;
    }
}
