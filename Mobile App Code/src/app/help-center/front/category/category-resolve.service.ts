import {Injectable} from '@angular/core';
import {ActivatedRouteSnapshot, Resolve, Router} from '@angular/router';
import {HelpCenterService} from '../../shared/help-center.service';
import {Category} from '../../../shared/models/Category';
import {Article} from '../../../shared/models/Article';
import {CategoriesService} from '../../shared/categories.service';
import {EMPTY, forkJoin, Observable, of} from 'rxjs';
import {catchError, mergeMap} from 'rxjs/operators';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';

@Injectable({
    providedIn: 'root',
})
export class CategoryResolve implements Resolve<{category: Category, articles: PaginationResponse<Article>}> {
    constructor(
        private helpCenter: HelpCenterService,
        private categories: CategoriesService,
        private router: Router,
    ) {}

    resolve(route: ActivatedRouteSnapshot): Observable<{category: Category, articles: PaginationResponse<Article>}> {
        const params = {
            categories: route.params.categoryId,
            orderBy: 'position|desc',
        };
        return forkJoin([
            this.categories.getCategory(route.params.categoryId),
            this.helpCenter.getArticles(params),
        ]).pipe(
            catchError(() => {
                this.router.navigate(['/help-center']);
                return EMPTY;
            }),
            mergeMap(response => {
                return of({category: response[0].category, articles: response[1].pagination});
            })
        );
    }
}
