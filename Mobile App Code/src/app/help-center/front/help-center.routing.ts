import {RouterModule, Routes} from '@angular/router';
import {HelpCenterComponent} from './help-center.component';
import {ArticleResolve} from '../shared/article/article-resolve.service';
import {HelpCenterResolve} from './help-center-resolve.service';
import {ArticleHostComponent} from './article-host/article-host.component';
import {CategoryResolve} from './category/category-resolve.service';
import {CategoryComponent} from './category/category.component';
import {HcSearchPageComponent} from './hc-search-page/hc-search-page.component';
import {HcSearchPageResolve} from './hc-search-page/hc-search-page-resolve.service';
import {NgModule} from '@angular/core';
import {CheckPermissionsGuard} from '@common/guards/check-permissions-guard.service';

export interface ArticleParams {
    parentId?: string;
    childId?: string;
    articleId: string;
}

const routes: Routes = [
    {path: 'help-center', canActivateChild: [CheckPermissionsGuard], children: [
        {
            path: '',
            component: HelpCenterComponent,
            resolve: {categories: HelpCenterResolve},
            data: {permissions: ['categories.view']}
        },
        {
            path: 'articles/:articleId/:slug',
            component: ArticleHostComponent,
            resolve: {articleResponse: ArticleResolve},
            data: {permissions: ['categories.view', 'articles.view']}
        },
        {
            path: 'articles/:parentId/:articleId/:slug',
            component: ArticleHostComponent,
            resolve: {articleResponse: ArticleResolve},
            data: {permissions: ['categories.view', 'articles.view']}
        },
        {
            path: 'articles/:parentId/:childId/:articleId/:slug',
            component: ArticleHostComponent,
            resolve: {articleResponse: ArticleResolve},
            data: {permissions: ['categories.view', 'articles.view']}
        },
        {
            path: 'categories/:categoryId/:slug',
            component: CategoryComponent,
            resolve: {resolves: CategoryResolve},
            data: {permissions: ['categories.view', 'articles.view']}
        },
        {
            path: 'search/:query',
            component: HcSearchPageComponent,
            resolve: {api: HcSearchPageResolve},
            data: {permissions: ['articles.view']}
        },
        {
            path: 'tickets',
            loadChildren: () => import('src/app/customer-mailbox/customer-mailbox.module').then(m => m.CustomerMailboxModule)
        },
        {
            path: 'manage',
            loadChildren: () => import('src/app/help-center/manage/help-center-manage.module').then(m => m.HcManageModule)
        },
    ]},
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})
export class HelpCenterRoutingModule {}
