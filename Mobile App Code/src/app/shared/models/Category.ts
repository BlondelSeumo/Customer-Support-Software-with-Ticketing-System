import {Article} from './Article';

export interface Category {
    id: number;
    name: string;
    description?: string;
    position: number;
    parent_id?: number;
    hidden: boolean;
    created_at?: string;
    updated_at?: string;
    children?: Category[];
    parent?: Category;
    articles?: Article[];
    chunked_articles?: (Article[])[];
    image?: string;
    articles_count?: number;
}
