import { Injectable } from '@angular/core';
import { Router, Resolve, ActivatedRouteSnapshot } from '@angular/router';
import {HelpCenterService, ShowArticleResponse} from '../help-center.service';
import {Article} from '../../../shared/models/Article';
import {ArticleParams} from '../../front/help-center.routing';

@Injectable({providedIn: 'root'})
export class ArticleResolve implements Resolve<ShowArticleResponse> {

    constructor(private helpCenter: HelpCenterService, private router: Router) {}

    resolve(route: ActivatedRouteSnapshot): Promise<ShowArticleResponse> {
        const params = route.params as ArticleParams;
        return this.helpCenter.getArticle(+params.articleId, {categories: params.parentId}).toPromise().then(response => {
            return response;
        }, () => {
            this.router.navigate(['/help-center']);
            return false;
        }) as any;
    }
}
