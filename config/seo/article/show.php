<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{URL.ARTICLE}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{ARTICLE.TITLE}} - {{SITE_NAME}}',
    ],
    [
        'property' => 'og:description',
        'content' => '{{ARTICLE.BODY}}',
    ],
    [
        'property' => 'og:type',
        'content' => 'article',
    ],
    [
        'property' => 'article:published_time',
        'content' => '{{ARTICLE.CREATED_AT}}',
    ],
    [
        'property' => 'article:modified_time',
        'content' => '{{ARTICLE.UPDATED_AT}}',
    ],
    [
        'property' => 'article:tags',
        'content' =>  [
            '_type' => 'loop',
            'dataSelector' => 'ARTICLE.TAGS',
            'template' => '{{TAG.NAME}}'
        ],
    ],
];