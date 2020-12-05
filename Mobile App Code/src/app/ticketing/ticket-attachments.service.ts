import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {Upload} from '../shared/models/Upload';
import {AppHttpClient} from '@common/core/http/app-http-client.service';

@Injectable({
    providedIn: 'root',
})
export class TicketAttachmentsService {
    constructor(private httpClient: AppHttpClient) {}

    public attach(conversationId: number, attachments: Upload[] = [], draft: string = null) {
        const payload: any = { ids: [] };

        payload.ids = attachments.map(attachment => attachment.id);

        if (draft) payload.body = draft;

        return this.httpClient.post('tickets/' + conversationId + '/attachments', payload);
    }

    public detach(draftId: number, uploadId: number): Observable<{data: number}> {
        return this.httpClient.post('drafts/' + draftId + '/uploads/' + uploadId + '/detach');
    }
}
