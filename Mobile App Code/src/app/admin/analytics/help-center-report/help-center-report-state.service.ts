import {Injectable} from '@angular/core';
import {HelpCenterReport, SearchTermReport} from './help-center-report';
import {BehaviorSubject, ReplaySubject} from 'rxjs';
import {Article} from '../../../shared/models/Article';

@Injectable({
    providedIn: 'root'
})
export class HelpCenterReportStateService {
    public failedSearches$ = new ReplaySubject<SearchTermReport[]>(1);
    public popularSearches$ = new ReplaySubject<SearchTermReport[]>(1);
    public popularArticles$ = new ReplaySubject<Partial<Article>[]>(1);
    public loading$ = new BehaviorSubject<boolean>(false);

    constructor() {}

    public set(report: HelpCenterReport) {
        this.failedSearches$.next(report.failed_searches);
        this.popularSearches$.next(report.popular_searches);
        this.popularArticles$.next(report.popular_articles);
    }
}
