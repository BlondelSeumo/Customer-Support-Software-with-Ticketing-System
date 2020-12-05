<?php

return [
    'remote_file_visibility' => env('REMOTE_FILE_VISIBILITY', 'public'),
    'static_file_delivery' => env('STATIC_FILE_DELIVERY', null),
    'uploads_disk_driver' => env('UPLOADS_DISK_DRIVER', 'local'),
    'public_disk_driver' => env('PUBLIC_DISK_DRIVER', 'local'),
    'version' => env('APP_VERSION'),
    'demo'    => env('IS_DEMO_SITE', false),
    'disable_update_auth' => env('DISABLE_UPDATE_AUTH', false),
    'enable_contact_page' => env('ENABLE_CONTACT_PAGE', false),
    'billing_integrated' => env('BILLING_ENABLED', false),
    'workspaces_integrated' => env('WORKSPACES_ENABLED', false),
    'notifications_integrated' => env('NOTIFICATIONS_ENABLED', false),
    'notif_subs_integrated' => env('NOTIF_SUBS_ENABLED', false),
    'api_integrated' => env('API_INTEGRATED', false),
    'enable_custom_domains' => env('ENABLE_CUSTOM_DOMAINS', false),
    'dynamic_app_url' => env('DYNAMIC_APP_URL', true),
    'hide_docs_buttons' => env('HIDE_DOCS_BUTTONS', false),
    'verify_paypal_webhook' => env('VERIFY_PAYPAL_WEBHOOK', false),
    'trust_all_proxies' => env('TRUST_ALL_PROXIES', false),
];
