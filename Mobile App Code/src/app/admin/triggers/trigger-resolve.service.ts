import { Injectable } from '@angular/core';
import { Router, Resolve, ActivatedRouteSnapshot } from '@angular/router';
import {TriggersService} from './triggers.service';
import {Trigger} from '../../shared/models/Trigger';

@Injectable({
    providedIn: 'root',
})
export class TriggerResolve implements Resolve<Trigger> {

    constructor(private triggers: TriggersService, private router: Router) {}

    resolve(route: ActivatedRouteSnapshot): Promise<Trigger> {
        return this.triggers.getTrigger(route.params['id']).toPromise().then(response => {
            return response.data;
        }, () => {
            this.router.navigate(['/admin/triggers']);
            return false;
        }) as any;
    }
}
