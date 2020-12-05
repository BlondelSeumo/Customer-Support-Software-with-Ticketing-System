import {Injectable} from '@angular/core';
import {Resolve, ActivatedRouteSnapshot} from '@angular/router';
import {CurrentUser} from '@common/auth/current-user';
import {Ticket} from '../../shared/models/Ticket';
import {Paginator} from '@common/shared/paginator.service';

@Injectable({
    providedIn: 'root',
})
export class CustomerTicketsListResolve implements Resolve<Object> {
    constructor(
        private currentUser: CurrentUser,
        private paginator: Paginator<Ticket>,
    ) {}

    resolve(route: ActivatedRouteSnapshot): any {
        const id  = this.currentUser.get('id');
        return this.paginator.paginate({}, 'users/' + id + '/tickets').subscribe(() => {
            return this.paginator;
        });
    }
}
