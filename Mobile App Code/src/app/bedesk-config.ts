import {AppConfig} from '@common/core/config/app-config';

export const BEDESK_CONFIG: AppConfig = {
    assetsPrefix: 'client',
    navbar: {
        defaultPosition: 'helpdesk-navbar',
        defaultColor: 'accent',
        dropdownItems: [
            {route: '/mailbox', name: 'Agent Mailbox', icon: 'inbox', permission: 'tickets.view'},
            {route: '/help-center/manage', name: 'Help Center', icon: 'description', permission: 'articles.create'},
            {route: '/help-center/tickets', name: 'My Tickets', icon: 'inbox', role: 'customers'},
        ]
    },
    auth: {
        redirectUri: '/help-center',
        adminRedirectUri: '/help-center',
        color: 'accent',
    },
    accountSettings: {
        hideNavbar: false,
    },
    customPages: {
        hideNavbar: false,
    },
    demo: {
      email: 'admin@demo.com',
      password: 'demo',
    },
    admin: {
        showIncomingMailMethod: true,
        analytics: {
            channels: [
                {name: 'conversations', route: '/admin/analytics/conversations'},
                {name: 'Search', route: '/admin/analytics/search'},
                {name: 'envato', route: '/admin/analytics/envato', condition: 'envato.enable'},
                {name: 'google', route: '/admin/analytics/google'},
            ]
        },
        settingsPages: [
            {route: 'ticketing', name: 'ticketing'},
            {route: 'help-center', name: 'help center'},
            {route: 'search', name: 'search'},
            {route: 'envato', name: 'envato'},
            {route: 'realtime', name: 'real time'},
        ],
        pages: [
            {route: 'triggers', permission: 'triggers.view', icon: 'device-hub', name: 'triggers'},
            {route: 'ticket-categories', permission: 'tags.view', icon: 'view-list', name: 'ticket categories'},
            {route: 'canned-replies', permission: 'canned_replies.view', icon: 'comment', name: 'canned replies'},
        ],
        tagTypes: [{name: 'status', system: true}, {name: 'category'}],
    },
};
