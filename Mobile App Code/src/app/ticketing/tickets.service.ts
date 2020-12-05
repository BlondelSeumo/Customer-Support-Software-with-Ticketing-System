import {Injectable} from '@angular/core';
import {Observable} from 'rxjs';
import {Ticket} from '../shared/models/Ticket';
import {Reply} from '../shared/models/Reply';
import {AppHttpClient} from '@common/core/http/app-http-client.service';
import {BackendResponse} from '@common/core/types/backend-response';
import {User} from '../shared/models/User';
import {Article} from '../shared/models/Article';
import {PaginationResponse} from '@common/core/types/pagination/pagination-response';
import {PaginatedBackendResponse} from '@common/core/types/pagination/paginated-backend-response';
import {PaginationParams} from '@common/core/types/pagination/pagination-params';
import {Tag} from '@common/core/types/models/Tag';

export interface DraftPayload {
    body?: string;
    uploads?: number[];
}

export interface SearchAllResponse {
    results: {
        tickets: PaginationResponse<Ticket>;
        users: PaginationResponse<User>;
        articles: PaginationResponse<Article>;
    };
}

@Injectable({
    providedIn: 'root',
})
export class TicketsService {
    constructor(private httpClient: AppHttpClient) { }

    public get(id: number, params = {}, options = {}): BackendResponse<{ticket: Ticket}> {
        return this.httpClient.get('tickets/' + id, params, options);
    }

    public create(payload: Partial<Ticket>): BackendResponse<{ticket: Ticket}> {
        return this.httpClient.post('tickets', payload);
    }

    public update(id: number, params: Partial<Ticket>): BackendResponse<{ticket: Ticket}> {
        return this.httpClient.put('tickets/' + id, params);
    }

    public saveDraft(ticketId: number, payload: DraftPayload, draftId?: number): BackendResponse<{data: Reply}> {
        if (draftId) {
            return this.httpClient.put('replies/' + draftId, payload);
        } else {
            return this.httpClient.post('tickets/' + ticketId + '/drafts', payload);
        }
    }

    public saveReply(ticketId: number, payload: Object): BackendResponse<{data: Reply}> {
        return this.httpClient.post('tickets/' + ticketId + '/replies', payload);
    }

    public getReply(replyId: number, params = {}, options = {}): BackendResponse<{reply: Reply}> {
        return this.httpClient.get('replies/' + replyId, params, options);
    }

    public getTicketReplies(ticketId: number, page = 1): PaginatedBackendResponse<Reply> {
        return this.httpClient.get('tickets/' + ticketId + '/replies', {page});
    }

    public addNote(ticketId: number, params: Object): BackendResponse<{data: Reply}> {
        return this.httpClient.post('tickets/' + ticketId + '/notes', params);
    }

    public updateReply(replyId: number, payload): BackendResponse<{data: Reply}> {
        return this.httpClient.put('replies/' + replyId, payload);
    }

    public search(query: string, params: PaginationParams & {query: string, detailed?: boolean}): BackendResponse<SearchAllResponse> {
        return this.httpClient.get('search/all', params);
    }

    public getTickets(params: {userId: number} & PaginationParams): PaginatedBackendResponse<Ticket> {
        return this.httpClient.get('tickets', params);
    }

    public addTag(tagName: string, ids: number[]): BackendResponse<{data: Tag}> {
        return this.httpClient.post('tickets/tags/add', {ids, tag: tagName});
    }

    public removeTag(tag: Tag, ids: number[]) {
        return this.httpClient.post('tickets/tags/remove', {ids, tag: tag.id});
    }

    public changeTicketStatus(ticketId: number, status: string) {
        return this.httpClient.post('tickets/status/change', {ids: [ticketId], status});
    }

    public changeMultipleTicketsStatus(ids: number[], newTag) {
        return this.httpClient.post('tickets/status/change', {ids, status: newTag.name});
    }

    public assign(ticketIds: number[], userId: number = null) {
        return this.httpClient.post('tickets/assign', {user_id: userId, tickets: ticketIds});
    }

    public deleteMultiple(ids: number[]) {
        return this.httpClient.delete('tickets', {ids});
    }

    public deleteDraft(id: number) {
        return this.httpClient.delete('drafts/' + id);
    }

    public deleteReply(id: number) {
        return this.httpClient.delete('replies/' + id);
    }

    public nextActiveTicket(tagId: string|number): BackendResponse<{ticket: Ticket}> {
        return this.httpClient.get(`tickets/${tagId}/next-active-ticket`);
    }

    /**
     * Get original email from which reply was created.
     */
    public getOriginalEmailForReply(id: number): Observable<{data: string}> {
        return this.httpClient.get('replies/' + id + '/original');
    }

    /**
     * Merge specified tickets.
     */
    public merge(id1: number, id2: number): Observable<Ticket> {
        return this.httpClient.post('tickets/merge/' + id1 + '/' + id2);
    }
}
