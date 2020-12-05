import {Injectable} from '@angular/core';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {Tag} from '@common/core/types/models/Tag';
import {BehaviorSubject} from 'rxjs';
import {finalize} from 'rxjs/operators';

export const DEFAULT_MAILBOX_TAG_ID = 'unassigned';

@Injectable({
    providedIn: 'root'
})
export class MailboxTagsService {
    public allTags$ = new BehaviorSubject<Tag[]>([]);
    public categoryTags: Tag[] = [];
    public statusTags: Tag[] = [];
    public activeTagId$ = new BehaviorSubject<string|number>(null);
    public loading$ = new BehaviorSubject(false);
    public viewTags: Tag[];

    constructor(private httpClient: AppHttpClient) {
        if (!this.allTags$.value.length) {
            this.refresh();
        }
    }

    public setTags(tags: Tag[]) {
        if ( ! tags) return;
        this.allTags$.next(tags);
        this.statusTags = tags.filter(tag => tag.type === 'status');
        this.categoryTags = tags.filter(tag => tag.type === 'category');
        this.viewTags = tags.filter(tag => tag.type === 'view');
    }

    public refresh() {
        this.loading$.next(true);
        this.httpClient.get('tags/agent-mailbox')
            .pipe(finalize(() => this.loading$.next(false)))
            .subscribe((tags: Tag[]) => {
                this.setTags(tags);
            });
    }

    public setActiveTag(id?: number|string) {
        this.activeTagId$.next(id || DEFAULT_MAILBOX_TAG_ID);
    }

    public getTagByIdOrName(idOrName: string|number) {
        return this.allTags$.value.find(tag => {
            return tag.id === +idOrName || tag.name === idOrName;
        });
    }
}
