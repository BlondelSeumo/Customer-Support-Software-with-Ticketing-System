import {NgModule} from '@angular/core';
import {CommonModule} from '@angular/common';
import {FormsModule, ReactiveFormsModule} from '@angular/forms';
import {RouterModule} from '@angular/router';
import {HelpCenterComponent} from './help-center.component';
import {HelpCenterSharedModule} from '../shared/help-center-shared.module';
import {ArticleHostComponent} from './article-host/article-host.component';
import {BreadCrumbsComponent} from './breadcrumbs/breadcrumbs.component';
import {CategoryComponent} from './category/category.component';
import {HcHeaderComponent} from './hc-header/hc-header.component';
import {TopicsPanelComponent} from './topics-panel/topics-panel.component';
import {CustomerFooterComponent} from '../../shared/customer-footer/customer-footer.component';
import {HcSearchPageComponent} from './hc-search-page/hc-search-page.component';
import {HelpCenterRoutingModule} from './help-center.routing';
import {HcSidenavComponent} from './hc-sidenav/hc-sidenav.component';
import {TextEditorModule} from '@common/text-editor/text-editor.module';
import {MultiProductComponent} from './home/multi-product/multi-product.component';
import {ArticleContentComponent} from './article-host/article-content/article-content.component';
import {ArticleGridComponent} from './home/article-grid/article-grid.component';
import {SuggestedArticleDropdownModule} from '../suggested-articles-dropdown/suggested-article-dropdown.module';
import {MaterialNavbarModule} from '@common/core/ui/material-navbar/material-navbar.module';
import {MatIconModule} from '@angular/material/icon';
import {ImageOrIconModule} from '@common/core/ui/image-or-icon/image-or-icon.module';
import {TranslationsModule} from '@common/core/translations/translations.module';
import {CustomerFooterModule} from '../../shared/customer-footer/customer-footer.module';
import {NoResultsMessageModule} from '@common/core/ui/no-results-message/no-results-message.module';
import {LoadingIndicatorModule} from '@common/core/ui/loading-indicator/loading-indicator.module';

@NgModule({
    imports:      [
        CommonModule,
        FormsModule,
        ReactiveFormsModule,
        RouterModule,
        TextEditorModule,
        HelpCenterSharedModule,
        SuggestedArticleDropdownModule,
        HelpCenterRoutingModule,
        MaterialNavbarModule,
        ImageOrIconModule,
        TranslationsModule,
        CustomerFooterModule,
        NoResultsMessageModule,
        LoadingIndicatorModule,

        // material
        MatIconModule,
    ],
    declarations: [
        HelpCenterComponent,
        HcHeaderComponent,
        CategoryComponent,
        ArticleHostComponent,
        TopicsPanelComponent,
        HcSearchPageComponent,
        HcSidenavComponent,
        MultiProductComponent,
        ArticleContentComponent,
        ArticleGridComponent,
    ],
    exports:      [BreadCrumbsComponent, CustomerFooterComponent],
})
export class HelpCenterModule { }
