import {Injectable} from '@angular/core';
import {Router, Resolve, ActivatedRouteSnapshot, RouterStateSnapshot} from '@angular/router';
import {TicketsService} from '../tickets.service';
import {Users} from '@common/auth/users.service';
import {User} from '@common/core/types/models/User';
import {map} from 'rxjs/operators';
import {Observable} from 'rxjs';

@Injectable({
    providedIn: 'root',
})
export class UserProfileResolve implements Resolve<User> {

    constructor(private tickets: TicketsService, private users: Users, private router: Router) {}

    resolve(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<User> {
        return this.users.get(route.params.id, {with: 'tags,purchase_codes,secondary_emails,details'})
            .pipe(map(response => {
                if (response.user) {
                    return response.user;
                } else {
                    this.router.navigate(['/mailbox']);
                }
            }));
    }
}
