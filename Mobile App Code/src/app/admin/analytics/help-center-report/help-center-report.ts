import {Category} from '../../../shared/models/Category';
import {Article} from '../../../shared/models/Article';
import {Model} from '../../../../common/core/types/models/model';

export interface HelpCenterReport {
    failed_searches: SearchTermReport[];
    popular_searches: SearchTermReport[];
    popular_articles: Partial<Article>[];
}

export interface SearchTermReport extends Model  {
    term: string;
    last_seen: string;
    count: string;
    resulted_in_ticket: number;
    category: Partial<Category>;
}

