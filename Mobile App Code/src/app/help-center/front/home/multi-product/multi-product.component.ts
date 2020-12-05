import {Component, ViewEncapsulation, ChangeDetectionStrategy, Input} from '@angular/core';
import {Category} from '../../../../shared/models/Category';
import {HcUrls} from '../../../shared/hc-urls.service';
import {Article} from '../../../../shared/models/Article';
import {Settings} from '@common/core/config/settings.service';
import {HelpCenterHeaderContent} from '../../../../admin/appearance/help-center-home-appearance-panel/help-center-header-content';
import {ReplaySubject} from 'rxjs';

@Component({
    selector: 'multi-product',
    templateUrl: './multi-product.component.html',
    styleUrls: ['./multi-product.component.scss'],
    encapsulation: ViewEncapsulation.None,
    changeDetection: ChangeDetectionStrategy.OnPush
})
export class MultiProductComponent {
    public content$ = new ReplaySubject<HelpCenterHeaderContent>(1);
    @Input() categories: Category[] = [];

    constructor(
        public urls: HcUrls,
        public settings: Settings,
    ) {
        this.settings.all$().subscribe(() => {
            this.content$.next(settings.getJson('hc.header.appearance', {}));
        });
    }

    public getFirstArticle(category: Category): Article|null {
        // at least one article is attached to category itself
        if (category.articles && category.articles.length) {
            return category.articles[0];
        }

        // return first article attached to deepest child category
        let firstChild = category;
        while (firstChild.children && firstChild.children.length) {
            firstChild = firstChild.children[0];
        }
        return firstChild.articles[0];
    }
}
