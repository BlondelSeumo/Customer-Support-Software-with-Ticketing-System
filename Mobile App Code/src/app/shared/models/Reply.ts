import {Ticket} from './Ticket';
import {User} from './User';
import {FileEntry} from '@common/uploads/types/file-entry';

export class Reply {
    id: number;
    body: string;
    user_id: number;
    ticket_id: number;
    uuid?: string;
    type = 'replies';
    created_at?: string;
    updated_at?: string;
    uploads?: FileEntry[];
    ticket?: Ticket;
    user?: User;

    constructor(params: Object = {}) {
        for (const name in params) {
            this[name] = params[name];
        }
    }
}
