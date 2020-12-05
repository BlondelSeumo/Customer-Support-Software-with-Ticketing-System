<?php

return [
    'roles' => [
        [
            'extends' => 'users',
            'name' => 'customers',
            'permissions' => [
                'categories.view',
                'articles.view',
                'tickets.create',
                'files.create',
            ]
        ],
        [
            'extends' => 'guests',
            'name' => 'guests',
            'permissions' => [
                'categories.view',
                'articles.view',
            ]
        ],
        [
            'extends' => 'customers',
            'name' => 'agents',
            'default' => false,
            'guests' => false,
            'permissions' => [
                'files.view',
                'tickets.view',
                'tickets.update',
                'tickets.create',
                'tickets.delete',
                'replies.view',
                'replies.create',
                'replies.update',
                'replies.delete',
                'users.view',
                'access.admin',
                'canned_replies.view',
                'canned_replies.create',
                'actions.view',
                'conditions.view',
                'triggers.view',
                'triggers.create',
                'users.view',
                'localizations.view',
                'custom_pages.view',
                'files.create',
                'plans.view',
                'categories.view',
                'articles.view',
                'notifications.subscribe',
            ]
        ]
    ],
    'all' => [
        'articles' => [
            'articles.view',
            'articles.create',
            'articles.update',
            'articles.delete',
        ],

        'categories' => [
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',
        ],

        'tickets' => [
            'tickets.view',
            'tickets.create',
            'tickets.update',
            'tickets.delete',
        ],

        'replies' => [
            'replies.view',
            'replies.create',
            'replies.update',
            'replies.delete',
        ],

        'canned_replies' => [
            'canned_replies.view',
            'canned_replies.create',
            'canned_replies.update',
            'canned_replies.delete',
        ],

        'triggers' => [
            'triggers.view',
            'triggers.create',
            'triggers.update',
        ],

        'conditions' => [
            'conditions.view',
        ],

        'actions' => [
            'actions.view',
        ],

        'notifications' => [
            [
                'name' => 'notifications.subscribe',
                'description' => 'Allows agents to subscribe to various conversation notifications.',
            ],
        ]
    ],
];
