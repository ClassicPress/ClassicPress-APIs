<?php

/**
 * Query feature requests for ClassicPress via the petitions site.
 *
 * This API serves as a CORS compliant proxy service to fetch this data on
 * behalf of ClassicPress installations.
 */

/**
 * List posts on the petitions site.
 *
 * See: https://getfider.com/docs/api/#list-posts
 */
function fider_list_posts($view) {
    $query = http_build_query(['view' => $view, 'limit' => 10]);
    return fider_api_query('/posts?' . $query);
}

/**
 * List tags on the petitions site.
 *
 * See: https://getfider.com/docs/api/#list-tags
 */
function fider_list_tags() {
    return fider_api_query('/tags');
}

/**
 * Simulate a Fider (petitions site) API call.
 *
 * Since petitions.classicpress.net is shut down as of December 31, 2020,
 * known responses are now pulled from static .json files.
 *
 * DO NOT add code that results in any changes to the API endpoints called OR
 * their arguments, otherwise this code will fail!
 */
function fider_api_query($endpoint) {
    $filename = __DIR__ . '/fider-' . trim(preg_replace('#[^a-z0-9]+#', '-', $endpoint), '-') . '.json';

    if (!file_exists($filename)) {
        return ['error' => 404];
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

$results = [];

foreach (['most-wanted', 'trending', 'recent'] as $view) {
    $posts = fider_list_posts($view);
    if (isset($posts['error'])) {
        $posts['operation'] = 'get:posts:' . $view;
        send_response($posts, true);
    }
    $results[$view] = [
        'data' => [],
        'link' => "https://petitions.classicpress.net/?view=$view",
    ];
    foreach ($posts as $post) {
        $results[$view]['data'][] = [
            'title' => $post['title'],
            'description' => $post['description'],
            'createdAt' => $post['createdAt'],
            'createdBy' => $post['user']['name'],
            'votesCount' => $post['votesCount'],
            'commentsCount' => $post['commentsCount'],
            'status' => $post['status'],
            'tags' => $post['tags'],
            'link' => "https://petitions.classicpress.net/posts/$post[number]/$post[slug]",
        ];
    }
}

$tags = fider_list_tags();
if (isset($tags['error'])) {
    $tags['operation'] = 'get:tags';
    send_response($tags, true);
}

$results['tags'] = [];
foreach ($tags as $tag) {
    $results['tags'][$tag['slug']] = [
        'name' => $tag['name'],
        'color' => $tag['color'],
    ];
}

$results['link'] = 'https://petitions.classicpress.net/';

send_response($results);
