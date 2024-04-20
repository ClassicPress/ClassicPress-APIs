<?php
/**
 * Get issues from the ClassicPress GitHub repository that are labeled as feature requests.
 */

$github_owner    = 'ClassicPress';
$github_repo     = 'ClassicPress';
$github_app_name = '';
$github_token    = '';

/**
 * If empty string is returned, then the issue has no label.
 *
 * @param $issue GH Issue.
 *
 * @return string Label with status: in it.
 */
function get_label_status( $issue ) {
    $label = array_reduce($issue['labels'], function ($carry, $item) {
        return strpos($item['name'], 'status:') === 0 ? $item['name'] : $carry;
    }, '');

    return str_replace('status:', '', $label);
}

/**
 * @param string $url             GitHub API URL.
 * @param string $github_token    GitHub token.
 * @param string $github_app_name GitHub app name.
 *
 * @return array API data returned.
 */
function http_get_request($url, $github_token, $github_app_name) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/vnd.github+json',
        'Authorization: Bearer ' . $github_token,
        'X-GitHub-Api-Version: 2022-11-28',
        'User-Agent: ' . $github_app_name
    ));
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );

    $response    = curl_exec( $ch );
    $status_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );

    if ( $status_code !== 200 ) {
        return [ 'error' => $status_code ];
    } else {
        return json_decode( $response, true );
    }

    curl_close($ch);
    return $response;
}

$url      = "https://api.github.com/repos/{$github_owner}/{$github_repo}/issues";
$responseArray = http_get_request( $url, $github_token, $github_app_name );

// Implement some kind of cache here to avoid hitting the GitHub API rate limit.
if ( ! is_countable( $responseArray ) || count( $responseArray ) < 1 || isset( $responseArray['error'] ) ) {
    $json = [
        'data'       => 'No feature requests found.',
        'error_code' => $responseArray['error'],
    ];
} else {
    $featureRequests = [];

    foreach ($responseArray as $issue) {
        $featureRequests[] = [
            'title'  => $issue['title'],
            'status' => trim( get_label_status( $issue ) ), // get a string with status: in it
            'link'   => $issue['html_url'],
        ];
    }

    $json['feature-requests'] = [
        'recent' => [
            'data' => $featureRequests,
        ],
        'link' => "https://github.com/{$github_owner}/{$github_repo}/issues?q=is%3Aopen+is%3Aissue+label%3A%22type%3A+feature+request%22",
    ];
}

header('Content-Type: application/json');
echo json_encode($json);
