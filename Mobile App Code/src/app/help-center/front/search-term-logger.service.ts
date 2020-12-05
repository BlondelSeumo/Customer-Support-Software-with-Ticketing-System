import {Injectable} from '@angular/core';
import {NavigationEnd, Router} from '@angular/router';
import {filter, finalize} from 'rxjs/operators';
import {Article} from '../../shared/models/Article';
import {Settings} from '@common/core/config/settings.service';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {objToFormData} from '@common/core/utils/obj-to-form-data';
import {CurrentUser} from '@common/auth/current-user';

const BACKEND_URI = 'search-term';

interface SearchSession {
    term: string;
    resultCount: number;
    clickedArticle: boolean;
    createdTicket: boolean;
    source: 'beacon' | 'hxr';
    page: 'ticket'|'hc';
    userId?: number;
    categoryId?: number;
    _token?: string;
}

@Injectable({
    providedIn: 'root'
})
export class SearchTermLoggerService {
    private session: SearchSession;

    constructor(
        private router: Router,
        private settings: Settings,
        private http: AppHttpClient,
        private currentUser: CurrentUser,
    ) {}

    public log(term: string, results: Article[], categories: number[] = []) {
        // if previous search term starts with current term
        // we can assume user did not finish typing yet
        if (this.isSameSearchSession(term)) {
            this.updateSession({
                term: term,
                resultCount: results.length,
                categoryId: (categories && categories[0]) || null,
            });
        } else {
            const payload = {...this.session, type: 'xhr ns'};
            this.resetSession();
            this.store(payload);
        }
    }

    public updateSession(data: Partial<SearchSession>) {
        this.session = {...this.session, ...data};
    }

    public updateSessionAndStore(data: Partial<SearchSession>) {
        this.updateSession(data);
        this.store();
    }

    private isSameSearchSession(term: string) {
        return !this.session.term ||
            term.indexOf(this.session.term) === 0 ||
            this.session.term.indexOf(term) === 0;
    }

    private store(payload?: SearchSession) {
        payload = payload || {...this.session};
        if ( ! payload.term || payload.term.length < 3) return;
        payload.source = payload.source || 'hxr';
        payload.userId = this.currentUser.get('id');
        payload.page = this.router.url.includes('tickets/new') ? 'ticket' : 'hc';
        this.http.post(BACKEND_URI, payload)
            .pipe(finalize(() => this.resetSession()))
            .subscribe();
    }

    public init() {
        this.resetSession();
        this.router.events.pipe(
            filter(e => e instanceof NavigationEnd)
        ).subscribe(() => {
            this.store();
        });

        if (navigator.sendBeacon) {
            window.addEventListener('beforeunload', () => {
                const payload = {...this.session};
                payload._token = this.settings.csrfToken;
                payload.source = 'beacon';
                if (payload.term) {
                    navigator.sendBeacon(
                        this.settings.getBaseUrl() + 'secure/' + BACKEND_URI,
                        objToFormData(payload),
                    );
                }
            });
        }
    }

    private resetSession() {
        this.session = {
            term: '',
            resultCount: 0,
            clickedArticle: false,
            createdTicket: false,
            source: null,
            page: 'hc',
            _token: null,
        };
    }
}
