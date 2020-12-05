import {Component, Input} from '@angular/core';
import {Category} from '../../../../shared/models/Category';
import {HcUrls} from '../../../shared/hc-urls.service';
import {Settings} from '@common/core/config/settings.service';

@Component({
    selector: 'article-grid',
    templateUrl: './article-grid.component.html',
    styleUrls: ['./article-grid.component.scss'],
})
export class ArticleGridComponent {
    @Input() categories: Category[] = [];

    constructor(
        public urls: HcUrls,
        public settings: Settings,
    ) {}

    public shouldHideCategory(category: Category): boolean {
        if (!category.articles) return true;
        return this.settings.get('hc_home.hide_small_categories') &&
            category.articles.length < 2;
    }
}
