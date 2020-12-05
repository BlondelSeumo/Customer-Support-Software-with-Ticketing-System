import {Injectable} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {Article} from '../../shared/models/Article';
import {Category} from '../../shared/models/Category';
import {slugifyString} from '@common/core/utils/slugify-string';

@Injectable({
    providedIn: 'root'
})
export class HcUrls {
    constructor(public route: ActivatedRoute) {}

    public article(article: Article, category?: Category): any[] {
        const base = ['/help-center/articles'] as any[];

        if ( ! category && article.categories) {
            category = article.categories[0];
        }

        if (category) {
            if (category.parent_id) base.push(category.parent_id);
            base.push(category.id);
        }

        return base.concat([article.id, this.getSlug(article)]);
    }

    public category(category: Category): any[] {
        return ['/help-center/categories', category.id, this.getSlug(category)];
    }

    public customerTicketList() {
        return ['/help-center/tickets'];
    }

    public searchPage(query: string) {
        return ['/help-center/search', query];
    }

    private getSlug(resource: {id: number, title?: string, name?: string, slug?: string}) {
        if (resource.slug) {
            return resource.slug;
        } else {
            return slugifyString(resource.title || resource.name);
        }
    }
}
