import {Injectable} from '@angular/core';
import {ActivatedRouteSnapshot, Resolve, Router} from '@angular/router';
import {HelpCenterService} from '../../shared/help-center.service';
import {Article} from '../../../shared/models/Article';
import {CategoriesService} from '../../shared/categories.service';
import {EMPTY, of} from 'rxjs';
import {catchError, map, mergeMap} from 'rxjs/operators';

@Injectable({
    providedIn: 'root',
})
export class NewArticleResolve implements Resolve<{article: Article}> {
    constructor(
        private helpCenter: HelpCenterService,
        private categories: CategoriesService,
        private router: Router
    ) {}

    resolve(route: ActivatedRouteSnapshot): any {
        return this.helpCenter.getArticle(route.params.article_id)
            .pipe(map(response => response.article))
            .pipe(
                catchError(() => {
                    this.router.navigateByUrl('/help-center/manage/articles');
                    return EMPTY;
                }),
                mergeMap((article: Article) => {
                    return of(article);
                })
            );
    }
}
