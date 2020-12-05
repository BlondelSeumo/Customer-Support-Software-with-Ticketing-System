<?php

return [
    'roles' => [
        [
            'default' => true,
            'name' => 'users',
            'permissions' => [
                'users.view',
                'localizations.view',
                'custom_pages.view',
                'files.create',
                'plans.view',
                'tags.view',
            ]
        ],
        [
            'guests' => true,
            'name' => 'guests',
            'permissions' => [
                'users.view',
                'custom_pages.view',
                'plans.view',
                'tags.view',
            ]
        ],
    ],
    'all' => [
        'admin' => [
            [
                'name' => 'admin.access',
                'description' => 'Required in order to access any admin area page.',
            ],
            [
                'name' => 'appearance.update',
                'description' => 'Allows access to appearance editor.'
            ]
        ],

        'roles' => [
            'roles.view',
            'roles.create',
            'roles.update',
            'roles.delete',
        ],

        'analytics' => [
            [
                'name' => 'reports.view',
                'description' => 'Allows access to analytics page.',
            ]
        ],

        'custom_pages' => [
            'custom_pages.view',
            [
                'name' => 'custom_pages.create',
                'restrictions' => [
                    [
                        'name' => 'count',
                        'type' => 'number',
                        'description' => __('policies.count_description', ['resources' => 'pages'])
                    ]
                ]
            ],
            'custom_pages.update',
            'custom_pages.delete',
        ],

        'custom_domains' => [
            'custom_domains.view',
            [
                'name' => 'custom_domains.create',
                'restrictions' => [
                    [
                        'name' => 'count',
                        'type' => 'number',
                        'description' => __('policies.count_description', ['resources' => 'domains'])
                    ]
                ]
            ],
            'custom_domains.update',
            'custom_domains.delete',
        ],

        'files' => [
            'files.view',
            'files.create',
            'files.delete',
        ],

        'users' => [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
        ],

        'localizations' => [
            'localizations.view',
            'localizations.create',
            'localizations.update',
            'localizations.delete',
        ],

        'mail_templates' => [
            'mail_templates.view',
            'mail_templates.update',
        ],

        'settings' => [
            'settings.view',
            'settings.update',
        ],

        'plans' => [
            'plans.view',
            'plans.create',
            'plans.create',
            'plans.delete',
        ],

        'invoices' => [
            'invoices.view',
        ],

        'tags' => [
            'tags.view',
            'tags.create',
            'tags.update',
            'tags.delete',
        ],

        'workspaces' => [
            'workspaces.view',
            [
                'name' => 'workspaces.create',
                'restrictions' => [
                    [
                        'name' => 'count',
                        'type' => 'number',
                        'description' => __('policies.count_description', ['resources' => 'workspaces'])
                    ],
                    [
                        'name' => 'member_count',
                        'type' => 'number',
                        'description' => 'Maximum number of members workspace is allowed to have.',
                    ]
                ]
            ],
            'workspaces.update',
            'workspaces.delete'
        ],
        'workspace_members' => [
            [
                'name' => 'workspace_members.invite',
                'display_name' => 'Invite Members',
                'type' => 'workspace',
                'description' => 'Allow user to invite new members into a workspace.',
            ],
            [
                'name' => 'workspace_members.update',
                'display_name' => 'Update Members',
                'type' => 'workspace',
                'description' => 'Allow user to change role of other members.',
            ],
            [
                'name' => 'workspace_members.delete',
                'display_name' => 'Delete Members',
                'type' => 'workspace',
                'description' => 'Allow user to remove members from workspace.',
            ]
        ]
    ]
];
