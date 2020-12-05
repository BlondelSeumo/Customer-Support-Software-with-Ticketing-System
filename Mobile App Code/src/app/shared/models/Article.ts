import {Category} from './Category';
import {ArticleFeedback} from './ArticleFeedback';
import {FileEntry} from '@common/uploads/types/file-entry';
import {Tag} from '@common/core/types/models/Tag';

export class Article {
    id: number;
    title: string;
    body: string;
    slug?: string;
    extra_data?: string;
    draft: boolean;
    visibility = 'public';
    views: number;
    position: number;
    description?: string;
    created_at?: string;
    updated_at?: string;
    categories?: Category[];
    tags?: Tag[];
    uploads?: FileEntry[];
    feedback?: ArticleFeedback[];
    score?: number;
    positive_votes?: number;
    negative_votes?: number;

    constructor(params: Object = {}) {
        for (const name in params) {
            this[name] = params[name];
        }
    }
}
