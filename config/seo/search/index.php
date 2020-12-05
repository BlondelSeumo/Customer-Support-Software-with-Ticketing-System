<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{URL.SEARCH}}',
    ],
    [
        'property' => 'og:title',
        'content' => 'Results for "{{QUERY}}" - {{SITE_NAME}}',
    ],
    [
        'property' => 'og:description',
        'content' => 'Search results for "{{QUERY}}"',
    ],
    [
        'property' => 'og:type',
        'content' => 'website',
    ],
];