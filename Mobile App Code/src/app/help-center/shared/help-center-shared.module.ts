import {NgModule} from '@angular/core';
import {HelpCenterService} from './help-center.service';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {CommonModule} from '@angular/common';
import {ArticleComponent} from './article/article.component';
import {ArticleModalComponent} from './article-modal/article-modal.component';
import {ArticleFeedbackComponent} from './article-feedback/article-feedback.component';
import {ArticleResolve} from './article/article-resolve.service';
import {BreadCrumbsComponent} from '../front/breadcrumbs/breadcrumbs.component';
import {RouterModule} from '@angular/router';
import {CategoriesService} from './categories.service';
import {UpdateArticleLinksDirective} from './article/update-article-links.directive';
import { MatAutocompleteModule } from '@angular/material/autocomplete';
import { MatDialogModule } from '@angular/material/dialog';
import { MatMenuModule } from '@angular/material/menu';
import {MatButtonModule} from '@angular/material/button';
import {MatIconModule} from '@angular/material/icon';
import {TranslationsModule} from '@common/core/translations/translations.module';

@NgModule({
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        RouterModule,
        TranslationsModule,

        // material
        MatButtonModule,
        MatIconModule,
        MatMenuModule,
        MatAutocompleteModule,
        MatDialogModule,
    ],
    declarations: [
        ArticleComponent,
        ArticleModalComponent,
        ArticleFeedbackComponent,
        BreadCrumbsComponent,
        UpdateArticleLinksDirective,
    ],
    exports:      [
        ArticleComponent,
        ArticleModalComponent,
        ArticleFeedbackComponent,
        BreadCrumbsComponent,
    ],
    providers: [
        HelpCenterService,
        CategoriesService,
        ArticleResolve,
    ],
})
export class HelpCenterSharedModule { }
