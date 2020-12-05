import {Injectable} from '@angular/core';
import {Category} from '../../shared/models/Category';
import {BackendResponse} from '@common/core/types/backend-response';
import {HttpCacheClient} from '@common/core/http/http-cache-client';
import {LocalStorage} from '@common/core/services/local-storage.service';
import {Observable} from 'rxjs';

@Injectable({
    providedIn: 'root',
})
export class CategoriesService {
    private baseUri = 'help-center/categories';

    constructor(protected http: HttpCacheClient, protected storage: LocalStorage) {}

    public reorderCategories(ids: number[], parentId?: number) {
        return this.http.post(this.baseUri + '/reorder', {ids, parentId});
    }

    /**
     * Create a new help center category or update existing one.
     */
    public createOrUpdateCategory(payload): Observable<Category> {
        if (payload.id) {
            return this.http.put(this.baseUri + '/' + payload.id, payload);
        } else {
            return this.http.post(this.baseUri, payload);
        }
    }

    /**
     * Fetch all help center categories that current user has access to.
     */
    public getCategories(params: {query?: string, limit?: number} = {}): BackendResponse<{categories: Category[]}> {
        return this.http.getWithCache(this.baseUri, params);
    }

    /**
     * Fetch category matching given id.
     */
    public getCategory(categoryId: number, folderId: number = null, articleId: any = null): BackendResponse<{category: Category}> {
        const queryParams: any = {};
        if (folderId) queryParams.folder = folderId;
        if (articleId) queryParams.article = articleId;

        return this.http.get(this.baseUri + '/' + categoryId, queryParams);
    }

    /**
     * Delete specified help center category.
     */
    public deleteCategory(id: number) {
        this.storage.remove('selectedCategories');
        return this.http.delete(this.baseUri + '/' + id);
    }

    /**
     * Detach specified category from its parent.
     */
    public detachCategory(id: number) {
        return this.http.post(this.baseUri + '/' + id + '/detach-parent');
    }
}
