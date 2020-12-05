import {RouterModule} from '@angular/router';
import {NewArticleComponent} from './new-article/new-article.component';
import {NewArticleResolve} from './new-article/new-article-resolve.service';
import {ArticlesListComponent} from './articles-list/articles-list.component';
import {HelpCenterManageComponent} from './help-center-manage.component';
import {CategoriesListComponent} from './categories-list/categories-list.component';
import {AuthGuard} from '@common/guards/auth-guard.service';

export const routing = RouterModule.forChild([
    {
        path: '',
        component: HelpCenterManageComponent,
        canActivate: [AuthGuard],
        children: [
            {
                path: '',
                redirectTo: 'articles'
            },
            {
                path: 'articles',
                component: ArticlesListComponent,
                data: {permissions: ['categories.view', 'tags.view', 'articles.view', 'articles.create']}
            },
            {
                path: 'categories',
                component: CategoriesListComponent,
                data: {permissions: ['categories.view', 'categories.create']}
            },
            {
                path: 'articles/new',
                component: NewArticleComponent,
                data: {permissions: ['articles.create']}
            },
            {
                path: 'articles/:article_id/edit',
                component: NewArticleComponent,
                resolve: {article: NewArticleResolve},
                data: {permissions: ['articles.update']}
            },
        ]
    },
]);
