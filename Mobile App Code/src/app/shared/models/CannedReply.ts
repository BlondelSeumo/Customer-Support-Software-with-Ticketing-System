import {FileEntry} from '@common/uploads/types/file-entry';
import {User} from '@common/core/types/models/User';

export class CannedReply {
    id: number;
    name: string;
    body: string;
    user_id: number;
    shared = false;
    created_at?: string;
    updated_at?: string;
    uploads?: FileEntry[];
    user?: User;

    constructor(params: Object = {}) {
        for (const name in params) {
            this[name] = params[name];
        }
    }
}
