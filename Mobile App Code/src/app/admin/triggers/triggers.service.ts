import {Injectable} from '@angular/core';
import {Action} from '../../shared/models/Action';
import {Condition} from '../../shared/models/Condition';
import {Trigger} from '../../shared/models/Trigger';
import {HttpCacheClient} from '@common/core/http/http-cache-client';
import {Observable} from 'rxjs';

@Injectable({
    providedIn: 'root',
})
export class TriggersService {

    constructor(private http: HttpCacheClient) {}

    /**
     * Fetch specified trigger.
     */
    public getTrigger(id: number): Observable<{data: Trigger}> {
        return this.http.getWithCache('triggers/' + id);
    }

    /**
     * Fetch all conditions for triggers.
     */
    public getConditions(): Observable<Condition[]> {
        return this.http.getWithCache('triggers/conditions');
    }

    /**
     * Fetch all actions for triggers.
     */
    public getActions(): Observable<Action[]> {
        return this.http.getWithCache('triggers/actions');
    }

    /**
     * Create a new trigger.
     */
    public create(payload: Object): Observable<Trigger> {
        return this.http.post('triggers', payload);
    }

    /**
     * Update existing trigger.
     */
    public update(payload: Object): Observable<Trigger> {
        return this.http.put('triggers/' + payload['id'], payload);
    }

    /**
     * Delete specified triggers.
     */
    public delete(ids: number[]): Observable<number[]> {
        return this.http.delete('triggers', {ids});
    }

    /**
     * Get value options for trigger action or condition.
     *
     * @param name
     */
    public getValueOptions(name: string): Observable<Object> {
        return this.http.getWithCache('triggers/value-options/' + name);
    }
}
