import {Injectable} from '@angular/core';
import {NavigationEnd, Router} from '@angular/router';
import {filter} from 'rxjs/operators';

@Injectable({
    providedIn: 'root'
})
export class RouterHistory {

    /**
     * Application route history.
     */
    private history: {url: string, queryParams?: Object}[] = [];

    /**
     * Maximum number of history items.
     */
    private  limit = 15;

    /**
     * RouteHistory Constructor.
     */
    constructor(private router: Router) {}

    /**
     * get history item at specified index or first item.
     */
    public get(index = 0) {
        return this.history[index] || null;
    }

    /**
     * Get previous history item.
     */
    public getPrevious() {
        return this.get(1);
    }

    /**
     * Navigate to previous url in routing history.
     */
    public back(): Promise<boolean> {
        const item = this.getPrevious();
        if ( ! item) return new Promise(resolve => resolve(false));

        return this.router.navigate([item.url], {queryParams: item.queryParams})
    }

    /**
     * Add new route history item from specified uri.
     */
    private addHistoryItem(uri: string) {
        const parts = uri.split('?');
        let queryParams = {};

        // parse and add query params
        if (parts[1]) {
            queryParams = this.parseQueryParams(parts[1]);
        }

        // limit history length
        if (this.history.length >= this.limit) {
            this.history.splice(1, this.history.length - 1);
        }

        this.history.unshift({url: parts[0], queryParams});
    }

    /**
     * Parse query params string into object.
     */
    private parseQueryParams(rawString: string): Object {
        const params = new URLSearchParams(rawString), queryParams = {};

        console.log(params);

        return queryParams;
    }

    /**
     * Initiate RouteHistory service.
     */
    public init() {
        this.router.events
            .pipe(filter(event => event instanceof NavigationEnd))
            .subscribe((e: NavigationEnd) => {
                this.addHistoryItem(e.urlAfterRedirects);
            });
    }
}
