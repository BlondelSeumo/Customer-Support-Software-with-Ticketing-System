import {Category} from '../../shared/models/Category';
import {strContains} from '@common/core/utils/str-contains';

export class CategoriesFilterer {

    /**
     * Filter categories by specified search query.
     */
    public filter(query: string = null, categories: Category[]): Category[] {
        if ( ! query) return categories.slice();

        const filtered = [];

        for (let i = 0; i < categories.length; i++) {
            const category = Object.assign({}, categories[i]);

            // if category name contains query, push it with all children
            if (strContains(categories[i].name, query)) {
                filtered.push(category);

            // if one of categories children names contain query,
            // push category only with that child
            } else {
                category.children = category.children.filter(child => {
                    return strContains(child['name'], query);
                });

                if (category.children.length) {
                    filtered.push(category);
                }
            }
        }

        return filtered;
    }
}
