<?php

/**
 * List feature requests for ClassicPress via the petitions posted on the
 * ClassicPress forums.
 *
 * This API endpoint serves as a CORS-compliant proxy service to fetch, cache
 * and serve this data on behalf of ClassicPress installations.
 */

/**
 * Load a cached JSON file or send an error response if it does not exist.
 */
function load_cached_json($slug) {
    $filename = __DIR__ . "/$slug.json";
    if (!file_exists($filename)) {
        error_log("File not found: $filename");
        send_response(['error' => 'Internal server error'], true);
    }
    return json_decode(file_get_contents($filename), true);
}

/**
 * Send a JSON response with the headers to relax CORS restrictions.
 */
function send_response($data, $is_error = false) {
    if ($is_error) {
        header('HTTP/1.1 500 Internal Server Error');
    }
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Max-Age: 3600');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Content-Type: application/json');
    die(json_encode($data));
}

$results = [
    'most-wanted' => load_cached_json('petitions-order-votes'),
    'trending'    => load_cached_json('petitions-order-latest'),
    'recent'      => load_cached_json('petitions-order-created'),
    'tags'        => [],
    'link'        => 'https://forums.classicpress.net/c/governance/petitions/77',
];

/**
 * The 'tags' field is not used by ClassicPress, but it is retained to avoid
 * breaking anything else that might be looking at this API.
 */
$tags = load_cached_json('fider-tags');
foreach ($tags as $tag) {
    $results['tags'][$tag['slug']] = [
        'name' => $tag['name'],
        'color' => $tag['color'],
    ];
}

send_response($results);
