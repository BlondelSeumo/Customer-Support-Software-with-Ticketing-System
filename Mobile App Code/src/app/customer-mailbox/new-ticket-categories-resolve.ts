import {Injectable} from '@angular/core';
import {Resolve, ActivatedRouteSnapshot, Router} from '@angular/router';
import {HttpCacheClient} from '@common/core/http/http-cache-client';
import {catchError, mergeMap} from 'rxjs/operators';
import {EMPTY, Observable, of} from 'rxjs';
import {Tag} from '../shared/models/Tag';

@Injectable({
    providedIn: 'root'
})
export class NewTicketCategoriesResolve implements Resolve<Tag[]> {
    constructor(
        private router: Router,
        private http: HttpCacheClient,
    ) {}

    resolve(route: ActivatedRouteSnapshot): Observable<Tag[]> {
        return this.http.get('new-ticket/categories')
            .pipe(
                catchError(() => {
                    this.router.navigateByUrl('/');
                    return EMPTY;
                }),
                mergeMap((response: {tags: Tag[]}) => {
                    return of(response.tags);
                })
            );
    }
}
