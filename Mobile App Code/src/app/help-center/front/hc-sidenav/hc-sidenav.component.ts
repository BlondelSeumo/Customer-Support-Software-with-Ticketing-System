import {Component, ElementRef, Input, OnInit, ViewEncapsulation} from '@angular/core';
import {HelpCenterService} from '../../shared/help-center.service';
import {Category} from '../../../shared/models/Category';
import {HcUrls} from '../../shared/hc-urls.service';
import {ActivatedRoute} from '@angular/router';

@Component({
    selector: 'hc-sidenav',
    templateUrl: './hc-sidenav.component.html',
    styleUrls: ['./hc-sidenav.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class HcSidenavComponent implements OnInit {
    @Input() category: Category;
    public categories: Category[] = [];

    constructor(
        private helpCenter: HelpCenterService,
        public urls: HcUrls,
        private el: ElementRef,
        private route: ActivatedRoute,
    ) {}

    ngOnInit() {
        this.fetchCategories();
    }

    private fetchCategories() {
        if ( ! this.category) return;
        this.helpCenter.getDataForHelpCenterSidenav(this.category.id).subscribe(response => {
            this.categories = response.categories;
        });
    }

    public routeIsActive(type: 'category'|'article', id: number): boolean {
        const params = this.route.snapshot.params;

        if (type === 'category') {
            // if showing article, don't highlight category
            return !params.articleId && +params.categoryId === id;
        } else {
            return +params.articleId === id;
        }
    }
}
