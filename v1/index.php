<?php

$is_browser = false;
$accept = array_map('trim', explode(',', $_SERVER['HTTP_ACCEPT'] ?? '', 20));
foreach ($accept as $mime_type) {
    if (preg_match('#^text/html(;|$)#', $mime_type)) {
        $is_browser = true;
        break;
    }
}

$endpoints = [
    '/core/stable-check/1.0/',
    '/core/version-check/1.0/',
    '/events/1.0/',
    '/secret-key/1.0/salt/',
];

if ($is_browser) {
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
