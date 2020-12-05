import {HelpCenterHomeAppearancePanelComponent} from './help-center-home-appearance-panel/help-center-home-appearance-panel.component';
import {NewTicketAppearancePanelComponent} from './new-ticket-appearance-panel/new-ticket-appearance-panel.component';
import {AppearanceEditorConfig} from '../../../common/admin/appearance/appearance-editor-config.token';

export const APP_APPEARANCE_CONFIG: AppearanceEditorConfig = {
    defaultRoute: 'help-center',
    navigationRoutes: [
        'help-center',
        'mailbox',
        'account/settings',
        'page'
    ],
    menus: {
        positions: [
            'agent-mailbox',
            'admin-navbar',
            'header',
            'custom-page-navbar'
        ],
        availableRoutes: [
            '/help-center/tickets',
            '/help-center/manage',
            '/mailbox/tickets',
            'mailbox/tickets/search',
        ]
    },
    sections: [
        {
            route: '/help-center',
            position: 3,
            component: HelpCenterHomeAppearancePanelComponent,
            name: 'Help Center Homepage',
        },
        {
            route: '/help-center/tickets/new',
            position: 3,
            component: NewTicketAppearancePanelComponent,
            name: 'New Ticket Page',
        },
    ]
};
