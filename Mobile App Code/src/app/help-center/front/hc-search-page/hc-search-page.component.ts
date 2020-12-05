import {Component, OnInit, ViewEncapsulation} from '@angular/core';
import {ActivatedRoute} from '@angular/router';
import {HcUrls} from '../../shared/hc-urls.service';
import {Article} from '../../../shared/models/Article';
import {Settings} from '@common/core/config/settings.service';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';
import {SearchTermLoggerService} from '../search-term-logger.service';

@Component({
    selector: 'hc-search-page',
    templateUrl: './hc-search-page.component.html',
    styleUrls: ['./hc-search-page.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class HcSearchPageComponent implements OnInit {
    public query: string;
    public perPage: number;
    public results: Article[];

    constructor(
        private route: ActivatedRoute,
        public settings: Settings,
        public urls: HcUrls,
        private searchLogger: SearchTermLoggerService,
    ) {}

    ngOnInit() {
        this.route.params.subscribe(params => this.query = params.query);
        this.route.data.subscribe((data: {api: PaginationResponse<Article>}) => {
            this.results = data.api.data;
            this.perPage = data.api.per_page;
        });
    }

    public userClickedOnArticle() {
        this.searchLogger.updateSessionAndStore({clickedArticle: true});
    }
}
