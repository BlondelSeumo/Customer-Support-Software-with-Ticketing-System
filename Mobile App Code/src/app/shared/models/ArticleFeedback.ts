import {Article} from "./Article";

export class ArticleFeedback {
	id: number;
	was_helpful: boolean;
	comment?: string;
	article_id: number;
	user_id?: number;
	ip?: string;
	created_at?: string;
	updated_at?: string;
	article?: Article;

	constructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }
    }
}