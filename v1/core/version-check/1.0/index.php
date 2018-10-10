<?php

$json = [
    'offers' => [
        [
            'response' => 'latest',
            'download' => 'https://downloads.classicpress.net/release/classicpress-1.0.0.zip',
            'locale' => 'en_US',
            'packages' => [
                'full' => '',
                'no_content' => '',
                'new_bundled' => '',
                'partial' => false,
                'rollback' => false,
            ],
            'current' => '1.0.0',
            'version' => '1.0.0',
            'php_version' => '5.6.0',
            'mysql_version' => '5.0',
            'new_bundled' => '1.0.0',
            'partial_version' => false,
        ],
    ],
    'translations' => []
];

echo json_encode($json);
