import {SocialProfile} from './SocialProfile';
import {Group} from './Group';
import {Ticket} from './Ticket';
import {UserDetails} from './UserDetails';
import {Email} from './Email';
import {PurchaseCode} from './PurchaseCode';
import {Upload} from './Upload';
import {Reply} from './Reply';
import {CannedReply} from './CannedReply';
import {Tag} from './Tag';
import {Permission} from '@common/core/types/models/permission';

export class User {
    id: number;
    first_name?: string;
    last_name?: string;
    email: string;
    avatar?: string;
    language?: string;
    country?: string;
    timezone?: string;
    permissions?: Permission[];
    password?: string;
    remember_token?: string;
    created_at?: string;
    updated_at?: string;
    deleted_at?: string;
    display_name: string;
    has_password: boolean;
    social_profiles?: SocialProfile[];
    groups?: Group[];
    tickets?: Ticket[];
    details?: UserDetails;
    secondary_emails?: Email[];
    purchase_codes?: PurchaseCode[];
    uploads?: Upload[];
    replies?: Reply[];
    cannedReplies?: CannedReply[];
    tags?: Tag[];

    constructor(params: Object = {}) {
        for (let name in params) {
            this[name] = params[name];
        }
    }
}
