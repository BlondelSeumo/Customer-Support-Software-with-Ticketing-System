import {Injectable} from '@angular/core';
import {Article} from '../../shared/models/Article';
import {BackendResponse} from '@common/core/types/backend-response';
import {HttpCacheClient} from '@common/core/http/http-cache-client';
import {SearchTermLoggerService} from '../front/search-term-logger.service';
import {tap} from 'rxjs/operators';
import {Category} from '../../shared/models/Category';
import {PaginatedBackendResponse} from '@common/core/types/pagination/paginated-backend-response';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';
import {PaginationParams} from '@common/core/types/pagination/pagination-params';

export interface ShowArticleResponse {
    article: Article;
    contentNav: any;
}

@Injectable({
    providedIn: 'root',
})
export class HelpCenterService {
    constructor(
        public httpClient: HttpCacheClient,
        private searchTerm: SearchTermLoggerService,
    ) {}

    public findArticles(params: {query: string, categories?: number[], perPage?: number, bodyLimit?: number}): PaginatedBackendResponse<Article> {
        return this.httpClient.get('search/articles', params)
            .pipe(tap((response: {pagination: PaginationResponse<Article>}) => {
                this.searchTerm.log(params.query, response.pagination.data, params.categories);
            }));
    }

    /**
     * Create new help center article.
     */
    public createArticle(payload): BackendResponse<{data: Article}> {
        return this.httpClient.post('help-center/articles', payload);
    }

    public updateArticle(id: number, payload): BackendResponse<{data: Article}> {
        return this.httpClient.put('help-center/articles/' + id, payload);
    }

    /**
     * Submit user feedback about specified article.
     */
    public submitArticleFeedback(id: number, payload: Object) {
        return this.httpClient.post('help-center/articles/' + id + '/feedback', payload);
    }

    /**
     * Fetch help center articles.
     */
    public getArticles(params: {categories: number|string} & PaginationParams): PaginatedBackendResponse<Article> {
        return this.httpClient.get('help-center/articles', params);
    }

    /**
     * Get a single help center article matching given id.
     */
    public getArticle(id: number, params?): BackendResponse<ShowArticleResponse> {
        return this.httpClient.get('help-center/articles/' + id, params);
    }

    /**
     * Get categories, child categories and articles for help center front page.
     */
    public getDataForHelpCenterFrontPage(): BackendResponse<{categories: Category[]}> {
        return this.httpClient.getWithCache('help-center');
    }

    /**
     * Get child categories and articles for specified parent category.
     */
    public getDataForHelpCenterSidenav(categoryId: number): BackendResponse<{categories: Category[]}> {
        return this.httpClient.getWithCache('help-center/sidenav', {categoryId});
    }

    /**
     * Delete articles with given ids.
     */
    public deleteArticles(articleIds: number[]) {
        return this.httpClient.delete('help-center/articles', {ids: articleIds});
    }
}
