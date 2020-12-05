<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{URL.CATEGORY}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{CATEGORY.NAME}} - {{SITE_NAME}}',
    ],
    [
        'property' => 'og:description',
        'content' => '{{CATEGORY.DESCRIPTION}}',
    ],
    [
        'property' => 'og:type',
        'content' => 'website',
    ],
];