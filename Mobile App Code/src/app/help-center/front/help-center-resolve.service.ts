import {Injectable} from '@angular/core';
import {Resolve, ActivatedRouteSnapshot, RouterStateSnapshot} from '@angular/router';
import {HelpCenterService} from '../shared/help-center.service';
import {Category} from '../../shared/models/Category';
import {EMPTY, Observable, of} from 'rxjs';
import {catchError, mergeMap} from 'rxjs/operators';

@Injectable({
    providedIn: 'root',
})
export class HelpCenterResolve implements Resolve<Category[]> {

    constructor(private helpCenter: HelpCenterService) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<Category[]> {
        return this.helpCenter.getDataForHelpCenterFrontPage().pipe(
            catchError(() => {
                return EMPTY;
            }),
            mergeMap(response => {
                return of(response.categories);
            })
        );
    }
}
