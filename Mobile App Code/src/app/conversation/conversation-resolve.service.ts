import { Injectable } from '@angular/core';
import {Router, Resolve, ActivatedRouteSnapshot, RouterStateSnapshot} from '@angular/router';
import {TicketsService} from '../ticketing/tickets.service';
import {Ticket} from '../shared/models/Ticket';
import {EMPTY, Observable, of} from 'rxjs';
import {catchError, mergeMap} from 'rxjs/operators';

const FAILURE_REDIRECT_URI = '/help-center/tickets';

@Injectable({
    providedIn: 'root',
})
export class TicketResolve implements Resolve<Ticket> {

    constructor(private tickets: TicketsService, private router: Router) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<Ticket> {
        const ticketId = route.params['ticket_id'] || route.params['id'];
        return this.tickets.get(+ticketId).pipe(
            catchError(() => {
                this.router.navigate([FAILURE_REDIRECT_URI]);
                return EMPTY;
            }),
            mergeMap(response => {
                if (! response.ticket) {
                    this.router.navigate([FAILURE_REDIRECT_URI]);
                    return EMPTY;
                } else {
                    return of(response.ticket);
                }
            })
        );
    }
}
