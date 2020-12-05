import {Injectable} from '@angular/core';
import {ActivatedRouteSnapshot, Resolve, Router} from '@angular/router';
import {HelpCenterService} from '../../shared/help-center.service';
import {Article} from '../../../shared/models/Article';
import {Settings} from '@common/core/config/settings.service';
import {catchError, mergeMap} from 'rxjs/operators';
import {EMPTY, Observable, of} from 'rxjs';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';

@Injectable({
    providedIn: 'root',
})
export class HcSearchPageResolve implements Resolve<PaginationResponse<Article>> {

    constructor(
        private helpCenter: HelpCenterService,
        private router: Router,
        private settings: Settings,
    ) {}

    resolve(route: ActivatedRouteSnapshot): Observable<PaginationResponse<Article>> {
        const query   = route.params.query,
            perPage   = this.settings.get('hc.search_page.limit', 20),
            bodyLimit = this.settings.get('hc.search_page.body_limit', 300);
        return this.helpCenter.findArticles({query, perPage, bodyLimit, categories: [route.queryParams.categoryId]})
            .pipe(
                catchError(() => {
                    this.router.navigateByUrl('/help-center');
                    return EMPTY;
                }),
                mergeMap(response => {
                    return of(response.pagination);
                })
            );
    }
}
