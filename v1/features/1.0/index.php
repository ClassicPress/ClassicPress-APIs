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
 * Query the Fider (petitions site) API via cURL.
 *
 * See: https://getfider.com/docs/api/
 */
function fider_api_query($endpoint) {
    $api_endpoint = 'https://petitions.classicpress.net/api/v1' . $endpoint;


    $fch = curl_init($api_endpoint);
    curl_setopt($fch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($fch);
    $response = json_decode($response, true);

    $status_code = curl_getinfo($fch, CURLINFO_HTTP_CODE);
    if ($status_code !== 200) {
        return ['error' => $status_code];
    }
    if (!is_array($response)) {
        return ['error' => 'unknown'];
    }

    return $response;
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
    send_response($tags);
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
