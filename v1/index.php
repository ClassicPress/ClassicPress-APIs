<?php

require_once __DIR__ . '/functions.php';

$endpoints = [
    '/checksums/',
    '/core/importers/1.0/',
    '/core/importers/2.0/',
    '/core/stable-check/1.0/',
    '/core/version-check/1.0/',
    '/events/1.0/',
    '/features/1.0/',
    '/secret-key/1.0/salt/',
    '/migration/',
    '/twemoji/',
    '/upgrade/',
];

// Automatically add new core translations
$translations_dir = 'translations/core';
if ( is_dir( $translations_dir ) ) {
	$directory = scandir( $translations_dir );
	foreach ( $directory as $file ) {
		if( ! preg_match( '~^[0-9]+\.[0-9]\.?[0-9]*~', $file ) ) {
			continue;
		}
		if( ! is_file($translations_dir.'/'.$file.'/translations.json' ) ) {
			continue;
		}
		$endpoints[] = "/$translations_dir/$file/translations.json";
	}
}

sort($endpoints);

if (is_browser()) {
    echo "<h2>Endpoints on this server:</h2>\n";
    echo "<ul>\n";
    foreach ($endpoints as $endpoint) {
        echo "<li><a href=\"$endpoint\">$endpoint</a></li>\n";
    }
    echo "</ul>\n";
} else {
    header('Content-Type: application/json');
    echo json_encode(['endpoints' => $endpoints], JSON_UNESCAPED_SLASHES);
}
