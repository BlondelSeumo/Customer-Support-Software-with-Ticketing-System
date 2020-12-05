<?php

return [
    [
        'property' => 'og:url',
        'content' =>  '{{URL.PAGE}}',
    ],
    [
        'property' => 'og:title',
        'content' => '{{PAGE.TITLE}} - {{SITE_NAME}}',
    ],
    [
        'property' => 'og:description',
        'content' => '{{PAGE.BODY}}',
    ],
];
