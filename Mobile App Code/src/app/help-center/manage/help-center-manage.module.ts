import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {RouterModule} from '@angular/router';
import {NewArticleComponent} from './new-article/new-article.component';
import {ArticlesListFiltersComponent} from './articles-list/articles-list-filters/articles-list-filters.component';
import {ArticlesListComponent} from './articles-list/articles-list.component';
import {ArticleSettingsModalComponent} from './new-article/article-settings-modal/article-settings-modal.component';
import {CategoriesManagerComponent} from './categories-manager/categories-manager.component';
import {HelpCenterManageComponent} from './help-center-manage.component';
import {CategoryModalComponent} from './category-modal/category-modal.component';
import {CategoriesListComponent} from './categories-list/categories-list.component';
import {CategoryListItemComponent} from './categories-list/category-list-item/category-list-item.component';
import {routing} from './help-center-manage.routing';
import {HelpCenterSharedModule} from '../shared/help-center-shared.module';
import {TextEditorModule} from '@common/text-editor/text-editor.module';
import {MatDialogModule} from '@angular/material/dialog';
import {MatSlideToggleModule} from '@angular/material/slide-toggle';
import {DragDropModule} from '@angular/cdk/drag-drop';
import {MaterialNavbarModule} from '@common/core/ui/material-navbar/material-navbar.module';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {MatButtonModule} from '@angular/material/button';
import {MatIconModule} from '@angular/material/icon';
import {MatTooltipModule} from '@angular/material/tooltip';
import {NoResultsMessageModule} from '@common/core/ui/no-results-message/no-results-message.module';
import {LoadingIndicatorModule} from '@common/core/ui/loading-indicator/loading-indicator.module';
import {MatCheckboxModule} from '@angular/material/checkbox';
import {CustomScrollbarModule} from '@common/core/ui/custom-scrollbar/custom-scrollbar.module';
import {ReplyAttachmentListModule} from '../../shared/reply-attachment-list/reply-attachment-list.module';
import {UploadsModule} from '@common/uploads/uploads.module';
import {DatatableModule} from '@common/datatable/datatable.module';
import {TagsManagerModule} from '@common/tags/tags-manager/tags-manager.module';
import {MatButtonToggleModule} from '@angular/material/button-toggle';
import {MatChipsModule} from '@angular/material/chips';

@NgModule({
    imports: [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        RouterModule,
        TextEditorModule,
        HelpCenterSharedModule,
        MaterialNavbarModule,
        TranslationsModule,
        NoResultsMessageModule,
        LoadingIndicatorModule,
        CustomScrollbarModule,
        ReplyAttachmentListModule,
        TagsManagerModule,
        UploadsModule,
        routing,
        DatatableModule,

        // material
        MatDialogModule,
        MatSlideToggleModule,
        DragDropModule,
        MatButtonModule,
        MatIconModule,
        MatTooltipModule,
        MatCheckboxModule,
        MatButtonToggleModule,
        MatChipsModule,
    ],
    declarations: [
        HelpCenterManageComponent,
        ArticlesListComponent,
        ArticlesListFiltersComponent,
        CategoriesManagerComponent,
        NewArticleComponent,
        CategoryModalComponent,
        ArticleSettingsModalComponent,
        CategoriesListComponent,
        CategoryListItemComponent,
    ],
})
export class HcManageModule { }
