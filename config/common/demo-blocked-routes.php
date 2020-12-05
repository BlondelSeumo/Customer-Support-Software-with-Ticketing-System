<?php

return [
    // tags
    ['method' => 'DELETE', 'name' => 'tags/delete-multiple'],
    ['method' => 'PUT', 'name' => 'tags/{id}'],

    // tickets
    ['method' => 'DELETE', 'name' => 'replies/{id}'],
    ['method' => 'DELETE', 'name' => 'tickets/{id}'],
    ['method' => 'DELETE', 'name' => 'tickets'],
    ['method' => 'DELETE', 'name' => 'canned-replies'],
    ['method' => 'POST', 'name' => 'tickets/mail/incoming'],
    ['method' => 'POST', 'name' => 'tickets/mail/failed'],

    // triggers
    ['method' => 'DELETE', 'name' => 'triggers'],

    // help center
    ['method' => 'DELETE', 'name' => 'help-center/articles'],
    ['method' => 'DELETE', 'name' => 'help-center/categories/{id}'],
    ['method' => 'POST', 'name' => 'help-center/categories/{id}/detach-parent'],

    // admin
    ['method' => 'POST', 'name' => 'envato/items/import'],
    ['method' => 'GET',  'name' => 'reports/envato'],
    ['method' => 'GET',  'name' => 'envato/items/import'],
    ['method' => 'POST', 'name' => 'help-center/actions/import'],
    ['method' => 'POST', 'name' => 'help-center/actions/delete-unused-image'],
];