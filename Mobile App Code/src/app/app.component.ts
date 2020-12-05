import {Component, ElementRef, OnInit, Renderer2, ViewEncapsulation} from '@angular/core';
import {EchoService} from './shared/broadcasting/echo.service';
import {NavigationEnd, RouteConfigLoadEnd, RouteConfigLoadStart, Router} from '@angular/router';
import {BrowserEvents} from '@common/core/services/browser-events.service';
import {Settings} from '@common/core/config/settings.service';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {filter, take} from 'rxjs/operators';
import {MetaTagsService} from '@common/core/meta/meta-tags.service';
import {SearchTermLoggerService} from './help-center/front/search-term-logger.service';
import cssVars from 'css-vars-ponyfill';
import {CookieNoticeService} from '@common/gdpr/cookie-notice/cookie-notice.service';
import {CurrentUser} from '@common/auth/current-user';
import {CustomHomepage} from '@common/pages/shared/custom-homepage.service';

@Component({
    selector: 'app-root',
    templateUrl: './app.component.html',
    styleUrls: ['./app.component.scss'],
    encapsulation: ViewEncapsulation.None,
})
export class AppComponent implements OnInit {
    public isLoading = false;

    constructor (
        private el: ElementRef,
        private browserEvents: BrowserEvents,
        private renderer: Renderer2,
        private echo: EchoService,
        private settings: Settings,
        private router: Router,
        private httpClient: AppHttpClient,
        private customHomepage: CustomHomepage,
        private metaTags: MetaTagsService,
        private searchTerm: SearchTermLoggerService,
        private cookieNotice: CookieNoticeService,
        private currentUser: CurrentUser,
    ) { }

    ngOnInit() {
        this.settings.set('notif.subs.integrated', this.currentUser.hasPermission('tickets.view'));
        this.browserEvents.subscribeToEvents(this.el.nativeElement);
        this.settings.setHttpClient(this.httpClient);
        this.metaTags.init();

        // google analytics
        if (this.settings.get('analytics.tracking_code')) {
            this.triggerAnalyticsPageView();
        }

        this.customHomepage.select();
        this.searchTerm.init();
        this.enableTransitionOnChunkLoad();
        this.loadCssVariablesPolyfill();
        this.cookieNotice.maybeShow();

        this.currentUser.isLoggedIn$
            .pipe(filter(isLoggedIn => isLoggedIn), take(1))
            .subscribe(() => {
                this.echo.init();
            });
    }

    /**
     * Show a transition animation when chunks
     * are being lazy loaded by angular.
     */
    private enableTransitionOnChunkLoad() {
        this.router.events
            .subscribe(e => {
                if (e instanceof RouteConfigLoadStart) {
                    this.isLoading = true;
                } else if (e instanceof RouteConfigLoadEnd) {
                    this.isLoading = false;
                }
            });
    }

    private triggerAnalyticsPageView() {
        this.router.events
            .pipe(filter(e => e instanceof NavigationEnd))
            .subscribe((event: NavigationEnd) => {
                if ( ! window['ga']) return;
                window['ga']('set', 'page', event.urlAfterRedirects);
                window['ga']('send', 'pageview');
            });
    }

    private loadCssVariablesPolyfill() {
        const isNativeSupport = typeof window !== 'undefined' &&
            window['CSS'] &&
            window['CSS'].supports &&
            window['CSS'].supports('(--a: 0)');
        if ( ! isNativeSupport) {
            cssVars();
        }
    }
}
