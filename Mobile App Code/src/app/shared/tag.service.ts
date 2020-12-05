import {Injectable} from '@angular/core';
import {HttpCacheClient} from '@common/core/http/http-cache-client';
import {BackendResponse} from '@common/core/types/backend-response';
import {PaginatedBackendResponse} from '@common/core/types/pagination/paginated-backend-response';
import {PaginationParams} from '@common/core/types/pagination/pagination-params';
import {Tag} from '@common/core/types/models/Tag';

@Injectable({
    providedIn: 'root'
})
export class TagService {
    private baseUri = 'tags';

    constructor(private http: HttpCacheClient) {}

    public getTags(params?: PaginationParams): PaginatedBackendResponse<Tag> {
        return this.http.getWithCache(this.baseUri, params);
    }

    public createNew(data: Partial<Tag>): BackendResponse<{tag: Tag}> {
        return this.http.post(this.baseUri, data);
    }

    public update(id: number, data: Partial<Tag>): BackendResponse<{tag: Tag}> {
        return this.http.put(this.baseUri + '/' + id, data);
    }

    public deleteMultiple(ids: number[]) {
        return this.http.delete(this.baseUri + '/delete-multiple', {ids});
    }

    public search(query: string, params: PaginationParams = {}): PaginatedBackendResponse<Tag> {
        params = {
            ...{query, notType: 'status', perPage: 10},
            ...params
        };
        return this.http.get(this.baseUri, params);
    }
}
